<?php
/**
 * This file is the main script used for moving information
 * from projects to wikidata
 * @author Addshore
 *
 * This file expects a mysql table in the format below..
 *
 * CREATE TABLE `iwlink` (
 * `site` char(10) NOT NULL,
 * `lang` char(12) NOT NULL,
 * `namespace` smallint(6) NOT NULL,
 * `title` char(200) NOT NULL,
 * `links` smallint(6) DEFAULT NULL,
 * `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 * `log` char(250) DEFAULT NULL,
 * PRIMARY KEY (`site`,`lang`,`namespace`,`title`)
 * ) ENGINE=InnoDB DEFAULT CHARSET=utf8
 *
 **/

use Addframe\Entity;
use Addframe\Family;
use Addframe\Globals;
use Addframe\Mysql;
use Addframe\Page;
use Addframe\Site;
use Addframe\UserLogin;

require_once( dirname( __FILE__ ) . '/../init.php' );

$wm = new Family(
	new UserLogin( Globals::$config['user.addbot']['user'],
		Globals::$config['user.addbot']['password'] ), 'meta.wikimedia.org' );

$wikidata = $wm->getSiteFromSiteid( 'wikidatawiki' );

if( isset( $_SERVER['INSTANCEPROJECT'] ) && $_SERVER['INSTANCEPROJECT'] == 'tools' ){
	$dbHost = 'tools-db';
} else {
	$dbHost = 'localhost';
}
$db = new Mysql(
	$dbHost, '3306',
	Globals::$config['replica.my']['user'],
	Globals::$config['replica.my']['password'],
	Globals::$config['replica.my']['user'].'_wikidata_p' );

$dbQuery = $db->select( 'iwlink','*', null, array('ORDER BY' => 'updated ASC', 'LIMIT' => '100' ) );
$rows = $db->mysql2array( $dbQuery );
if( $rows === false ){
	die('Empty database?');
}

foreach ( $rows as $row ) {
	$log = '';
	echo "* Next page!\n";
	// Load our site
	$baseSite = $wm->getSiteFromSiteid( $row['lang'] . $row['site'] );

	// Get an array of all pages involved
	/* @var $usedPages Page[] */
	$usedPages = array();
	$usedPages[] = $baseSite->newPageFromTitle( $baseSite->getNamespaceFromId( $row['namespace'] ) . $row['title'] );
	$usedPages = array_merge( $usedPages, $usedPages[0]->getPagesFromInterwikiLinks() );
	$usedPages = array_merge( $usedPages, $usedPages[0]->getPagesFromInterprojectLinks() );
	$usedPages = array_merge( $usedPages, $usedPages[0]->getPagesFromInterprojectTemplates() );
	//@todo remove duplicate pages (maybe use an PageList?)

	// Try to find an entity to work on
	/* @var $page Page */
	echo "* Trying to find an entity to work on!\n";
	foreach ( $usedPages as $page ) {
		if ( $page->getEntity() instanceof Entity ) {
			$baseEntity = $page->getEntity();
			echo "* Found entity " . $baseEntity->id . "\n";
			break;
		}
	}

	// Are we still missing an entity?
	if ( ! isset( $baseEntity ) ) {
		echo "* Failed to find an entiy to work from\n";
		//We could create an entity to work on here... Instead we will continue;
		//We could also try 'linking titles' here?
		continue;
	}

	// Add everything to the entity
	echo "* Adding everything to the entity!\n";
	$baseEntity->load();
	foreach ( $usedPages as $page ) {
		$baseEntity->addSitelink( $page->site->getId(), $page->title );
		if ( $page->site->getType() == 'wiki' ) {
			$baseEntity->addLabel( $page->site->getLanguage(), $page->title );
		}
	}

	if( $baseEntity->changed === true ){
		echo "* Saving the entity!\n";
		$saveResult = $baseEntity->save();
		if( isset ( $saveResult['error']['code'] ) && $saveResult['error']['code'] == 'failed-save' ){
			$conflicts = array();
			$conflicts[] = $baseEntity->id;
			foreach( $saveResult['error']['messages'] as $messageKey => $errorMessage ){
				if( $messageKey == 'html' ){ continue; }
				if( $errorMessage['name'] == 'wikibase-error-sitelink-already-used' ){
					$conflicts[] = $errorMessage['parameters']['2'];
				}
			}
			$log .= "Conflict(".implode(', ', $conflicts).")";
		}
	}

	echo "* Removing links from the page!\n";
	if ( $usedPages[0]->removeEntityLinksFromText() == true ) {
		$usedPages[0]->save( getLocalSummary( $usedPages[0]->getSite(), $baseEntity->id) );

		$remaining = count( $usedPages[0]->getInterwikisFromtext() );
		echo "* $remaining interwiki links left on page\n";
		if( $remaining == 0 ){
			echo "* Deleting from database\n";
			$db->delete( 'iwlink', array(
				'lang' => $row['lang'],
				'site' => $row['site'],
				'namespace' => $row['namespace'],
				'title' => $row['title'])
			);
		} else {
			if( $usedPages[0]->isFullyEditProtected() ){
				$log = "Protected()".$log;
			}
			$db->update('iwlink', array( 'links' => $remaining, 'log' => $log ), array(
				'lang' => $row['lang'],
				'site' => $row['site'],
				'namespace' => $row['namespace'],
				'title' => $row['title'])
			);
		}
	}

	unset($baseEntity, $usedPages, $log);
	sleep(10);
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
	if( isset( Globals::$config['wbimport.summary'][ $language ] ) ){
		$summary = Globals::$config['wbimport.summary'][ $language ];
	}

	$summary = str_replace('$who', 'User:'.$bot, $summary);
	$summary = str_replace('$id', $id, $summary);
	return $summary;

}