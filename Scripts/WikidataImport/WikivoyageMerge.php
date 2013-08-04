<?php
/**
 * This file is the main script used for moving information
 * from projects to wikidata
 * @author Addshore
 **/

use Addframe\Mediawiki\Entity;
use Addframe\Mediawiki\Family;
use Addframe\Mediawiki\Page;
use Addframe\Mediawiki\UserLogin;
/**
 * @author Addshore
 */

require_once( dirname( __FILE__ ) . '/../../Init.php' );

//This is an array that we can keep our summaries in...
$summaries = array();

//Create a site
$wm = new Family( new UserLogin( 'addbot', 'password' ), 'meta.wikimedia.org' );
$wikidata = $wm->getSiteFromSiteid( 'wikidatawiki' );


$items = array( 'q14208163' );
foreach ( $items as $item ) {

	$itemOne = $wikidata->newEntityFromEntityId( $item );
	$itemOne->load();
	$itemTwo = null;

	$linkedPages = array();
	$guessedPages = array();
	foreach ( $itemOne->getLanguageData('sitelinks') as $siteid => $value ) {
		$site = $wm->getSiteFromSiteid( $siteid );
		$page = $site->newPageFromTitle( $value['title'] );
		$linkedPages = array_merge( $linkedPages, $page->getPagesFromInterprojectLinks() );
		$linkedPages = array_merge( $linkedPages, $page->getPagesFromInterprojectTemplates() );
		$guessedPages[] = $wm->getSiteFromSiteid( $page->site->getLanguage() . 'wiki' )->newPageFromTitle( $page->title );
	}

	/* @var $page Page */
	foreach ( array_merge( $linkedPages, $guessedPages ) as $page ) {
		$itemTwo = $page->getEntity();
		if ( $itemTwo instanceof Entity ) {
			$itemTwo->load();

			foreach ( $itemOne->getLanguageData('sitelinks') as $siteid => $value ) {
				$itemOne->removeSitelink( $siteid );
				$itemTwo->addSitelink( $siteid, $value['title'] );
			}

			break;
		}
	}

	if ( $itemOne->isChanged() && $itemTwo->isChanged() ) {
		$itemOne->save( 'Merging to ' . $itemTwo->getId() );
		$itemTwo->save( 'Adding sitelinks from ' . $itemTwo->getId() );
	}

}