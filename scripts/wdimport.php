<?php
/**
 * This file is the main script used for moving information
 * from projects to wikidata
 * @author Addshore
 **/

use Addframe\Entity;
use Addframe\Family;
use Addframe\Page;
use Addframe\UserLogin;

require_once( dirname( __FILE__ ) . '/../init.php' );

//This is an array that we can keep our summaries in...
$summaries = array();

//Create a site
$wmConfig = parse_ini_file('~/addbot.wm.cnf');
$wm = new Family( new UserLogin( $wmConfig['user'], $wmConfig['password'] ), 'meta.wikimedia.org' );
unset ( $wmConfig );
$wikidata = $wm->getSiteFromSiteid( 'wikidatawiki' );

//$dbConfig = parse_ini_file('~/replica.my.cnf');
//$db = new Mysql( 'tools-db', '3306', $dbConfig['user'], $dbConfig['password'], $dbConfig['user'].'wikidata_p' );
//unset( $dbConfig );
//$dbQuery = $db->select( 'iwlink','*',null,array('ORDER BY' => 'updated ASC', 'LIMIT' => '100' ) );
//$rows = $db->mysql2array( $dbQuery );
$rows = array( //array('lang' => 'en', 'site' => 'wiki', 'namespace' => '0', 'title' => 'À Beira do Caminho'),
	array( 'lang' => 'en', 'site' => 'wikivoyage', 'namespace' => '0', 'title' => 'Europe' ),
	//array( 'lang' => 'en', 'site' => 'wiki', 'namespace' => '0', 'title' => 'Pear' ),
	//array( 'lang' => 'en', 'site' => 'wiki', 'namespace' => '0', 'title' => 'Banana' ),
);
foreach ( $rows as $row ) {
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
	foreach ( $usedPages as $page ) {
		$baseEntity->addSitelink( $page->site->getId(), $page->title );
		if ( $page->site->getType() == 'wiki' ) {
			$baseEntity->addLabel( $page->site->getLanguage(), $page->title );
		}
	}

	echo "* Saving the entity!\n";
	$baseEntity->save();
	$baseEntity->load();

	echo "* Removing links from the page!\n";
	if ( $usedPages[0]->removeEntityLinksFromText() == true ) {
		$usedPages[0]->save(); //@todo localised edit summaries
	}

	//Count how many iwlinks are left on a page
	//Update the database

}