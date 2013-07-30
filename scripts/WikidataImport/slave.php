<?php
/**
 * This file is the main script used for moving information
 * from projects to wikidata
 * @author Addshore
 *
 **/

use Addframe\Entity;
use Addframe\Family;
use Addframe\Globals;
use Addframe\Mysql;
use Addframe\Page;
use Addframe\PageList;
use Addframe\Site;
use Addframe\Stathat;
use Addframe\UserLogin;

require_once( dirname( __FILE__ ) . '/../../init.php' );

$wm = new Family(
	new UserLogin( Globals::$config['wikiuser']['username'],
		Globals::$config['wikiuser']['password'] ), Globals::$config['wikiuser']['home'] );

$wikidata = $wm->getSiteFromSiteid( 'wikidatawiki' );

$db = new Mysql(
	Globals::$config['mysql']['server'], '3306',
	Globals::$config['mysql']['user'],
	Globals::$config['mysql']['password'],
	Globals::$config['mysql']['user'].'_wikidata_p' );

$stathat = new Stathat( Globals::$config['stathat']['key'] );

$redis = new Redis();
$redis->connect(Globals::$config['redis']['server']);
$redis->setOption(Redis::OPT_PREFIX, Globals::$config['redis']['prefix']);
$redis->select(9);

while(true){

	echo "* Getting next page from redis!\n";
	$row = $redis->brPop(Globals::$config['redis']['key'], 0);
	$row = json_decode( $row[1], true );

	// Load our site
	$stathat->stathat_ez_count( "Addbot - IW Removal - Articles Loaded", 1 );
	$log = '';
	$baseSite = $wm->getSiteFromSiteid( $row['lang'] . $row['site'] );

	// Get an array of all pages involved
	$usedPages = new PageList(
		$baseSite->newPageFromTitle( $baseSite->getNamespaceFromId( $row['namespace'] ) . $row['title'] ) );
	$usedPages->addArray( $usedPages->getPageWithkey(0)->getPagesFromInterwikiLinks() );
	$usedPages->addArray( $usedPages->getPageWithkey(0)->getPagesFromInterprojectLinks() );
	$usedPages->addArray( $usedPages->getPageWithkey(0)->getPagesFromInterprojectTemplates() );
	$usedPages->makeUniqueFromPage();

	// Try to find an entity to work on
	/* @var $page Page */
	echo "* Trying to find an entity to work on!\n";
	foreach ( $usedPages->toArray() as $page ) {
		if ( $page->getEntity() instanceof Entity ) {
			$baseEntity = $page->getEntity();
			echo "* Found entity " . $baseEntity->id . "\n";
			break;
		}
	}

	//If we have an entity try to update it
	if ( isset( $baseEntity ) ) {
		// Add everything to the entity
		echo "* Adding everything to the entity!\n";
		$baseEntity->load();
		foreach ( $usedPages->toArray() as $page ) {
			$baseEntity->addSitelink( $page->site->getId(), $page->normaliseTitleNamespace() );
			//@todo this should only happen for entity site links so should be in a different place
//			if ( $page->site->getType() == 'wiki' ) {
//				$baseEntity->addLabel( $page->site->getLanguage(), $page->title );
//			}
		}

		// If the entity is changed try to save it
		if( $baseEntity->changed === true ){
			echo "* Saving the entity!\n";
			$summary = "Adding links from ".$usedPages->getPageWithkey(0)->site->getId()." ".$usedPages->getPageWithkey(0)->title;
			$saveResult = $baseEntity->save( $summary );
			// If we get an error try to work around it
			if( isset ( $saveResult['error']['code'] ) && $saveResult['error']['code'] == 'failed-save' ){
				$conflicts = array();
				$conflicts[] = $baseEntity->id;
				foreach( $saveResult['error']['messages'] as $messageKey => $errorMessage ){
					if( $messageKey == 'html' ){ continue; }
					if( $errorMessage['name'] == 'wikibase-error-sitelink-already-used' ){
						$conflicts[] = $errorMessage['parameters']['2'];

						//Now remove it
						$errorUrl = strstr( trim( $errorMessage['parameters']['0'], '/') , '/' , true );
						$errorSite = $wm->getSite( $errorUrl );
						if( $errorMessage instanceof Site ){
							$baseEntity->removeSitelink( $errorSite->getId() );
						}

					}
				}
				$conflicts = array_unique( $conflicts );
				$log .= "Conflict(".implode(', ', $conflicts).")";
				$saveResult = $baseEntity->save( $summary );
			}
			
			if( !array_key_exists( 'error', $saveResult ) ){
				$stathat->stathat_ez_count( "Addbot - Wikidata Edits", 1 );
			}
		}
	} else {
		echo "* Failed to find an entiy to work from\n";
	}

	// Try to remove links from the article
	echo "* Removing links from the page!\n";
	$removed = $usedPages->getPageWithkey(0)->removeEntityLinksFromText();
	if ( $removed > 0 ) {
		$usedPages->getPageWithkey(0)->save( getLocalSummary( $usedPages->getPageWithkey(0)->getSite(), $usedPages->getPageWithkey(0)->getEntity()->id ), true );
		//@todo make sure the edit was a success before posting stats?
		$stathat->stathat_ez_count( "Addbot - IW Removal - Global Edits", 1 );
		$stathat->stathat_ez_count( "Addbot - IW Removal - Global Removals", $removed );
	}

	// Try to update the database
	$usedPages->getPageWithkey(0)->getText( true );
	$remaining = count( $usedPages->getPageWithkey(0)->getInterwikisFromtext() );
	echo "* $remaining interwiki links left on page\n";
	if( $remaining == 0 ){
		echo "* Deleting from database\n";
		$stathat->stathat_ez_count( "Addbot - IW Removal - DB Removal", 1 );
		$db->delete( 'iwlink', array(
				'lang' => $row['lang'],
				'site' => $row['site'],
				'namespace' => $row['namespace'],
				'title' => $row['title'])
		);
	} else {
		if( $usedPages->getPageWithkey(0)->isFullyEditProtected() ){
			$log = "Protected()".$log;
		}
		echo "* Updating in database\n";
		$db->update('iwlink',
			array( 'links' => $remaining, 'log' => $log , 'updated' => date( "Y-m-d H:i:s" ) ),
			array(
				'lang' => $row['lang'],
				'site' => $row['site'],
				'namespace' => $row['namespace'],
				'title' => $row['title'])
		);
	}

	// Try to reset stuff
	if( !empty( $log ) ){
		echo $log."\n";
	}

	unset($baseEntity, $usedPages, $log);
}

/**
 * @param Site $site (to get the language from)
 * @param $id string (to add to the summary)
 * @return string Localised Summary
 */
function getLocalSummary( Site $site , $id){
	$language = $site->getLanguage();
	$bot = $site->getUserLogin()->username;

	$summary = '[[$who|Bot]]: Migrating interwiki links, now provided by [[d:|Wikidata]] on [[d:$id]]';
	if( isset( Globals::$config['WikidataImport.Summary'][ $language ] ) ){
		$summary = Globals::$config['WikidataImport.Summary'][ $language ];
	}

	$summary = str_replace('$who', 'User:'.$bot, $summary);
	$summary = str_replace('$id', $id, $summary);
	return $summary;

}