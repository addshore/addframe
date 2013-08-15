<?php
/**
 * This file is the main script used for moving information
 * from projects to wikidata
 * @author Addshore
 **/
use Addframe\Mediawiki\Wikibase\Entity;
use Addframe\Mediawiki\Family;
use Addframe\Mediawiki\Page;
use Addframe\Mediawiki\UserLogin;
use Addframe\Config;

/**
 * @author Addshore
 */

require_once( dirname( __FILE__ ) . '/../../Init.php' );

//This is an array that we can keep our summaries in...
$summaries = array();

//Create a site
$wm = new Family(
	new UserLogin( Config::get( 'wikiuser', 'username'),
		Config::get( 'wikiuser', 'password') ), Config::get( 'wikiuser', 'home') );
$wikidata = $wm->getSite('www.wikidata.org');

for ( $i = 14587324; $i > 12000000; $i--) {
	$item = 'q'.$i;

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