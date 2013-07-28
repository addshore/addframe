<?php
/**
 * This file is the main script used for moving information
 * from projects to wikidata
 * @author Addshore
 **/

use Addframe\Entity;
use Addframe\Family;
use Addframe\Globals;
use Addframe\Mysql;
use Addframe\Page;
use Addframe\UserLogin;

require_once( dirname( __FILE__ ) . '/../init.php' );

//This is an array that we can keep our summaries in...
$summaries = array();

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
	echo "* Next page!\n";
	// Load our site
	$baseSite = $wm->getSiteFromSiteid( $row['lang'] . $row['site'] );

	// Get an array of all pages involved
	/* @var $usedPages Page[] */
	$usedPages = array();
	$usedPages[] = $baseSite->newPageFromTitle( $baseSite->getNamespaceFromId( $row['namespace'] ) .':'. $row['title'] );
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
		print_r( $baseEntity->save() );
	}

	echo "* Removing links from the page!\n";
	if ( $usedPages[0]->removeEntityLinksFromText() == true ) {
		$usedPages[0]->save(); //@todo localised edit summaries

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
			//update the db
			$db->update('iwlink', array( 'links' => $remaining), array(
				'lang' => $row['lang'],
				'site' => $row['site'],
				'namespace' => $row['namespace'],
				'title' => $row['title'])
			);
		}
	}

	unset($baseEntity,$usedPages);
}