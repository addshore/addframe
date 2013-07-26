<?php
/**
 * This file is the main script used for moving information
 * from projects to wikidata
 * @author Addshore
 **/

//Initiate the script
require_once( dirname(__FILE__).'/../init.php' );

//This is an array that we can keep our summaries in...
$summaries = array();

//Create a site
$wm = new Family('wikimedia',new UserLogin('addbot','password'),'meta.wikimedia.org');
$wikidata = $wm->getSiteFromSiteid('wikidatawiki');


$items = array('q14208163');
foreach($items as $item){

	$itemOne = $wikidata->getEntityFromId( $item );
	$itemOne->load();
	$itemTwo = null;

	$linkedPages = array();
	$guessedPages = array();
	foreach( $itemOne->languageData['sitelinks'] as $siteid => $value ){
		$site = $wm->getSiteFromSiteid( $siteid );
		$page = $site->getPage( $value['title'] );
		$linkedPages = array_merge( $linkedPages, $page->getPagesFromInterprojectLinks() );
		$linkedPages = array_merge( $linkedPages, $page->getPagesFromInterprojectTemplates() );
		$guessedPages[] = $wm->getSiteFromSiteid( $page->site->lang.'wiki' )->getPage( $page->title );
	}

	/* @var $page Page */
	foreach( array_merge( $linkedPages, $guessedPages ) as $page ){
		$itemTwo = $page->getEntity();
		if( $itemTwo instanceof WikibaseEntity ){
			$itemTwo->load();

			foreach( $itemOne->languageData['sitelinks'] as $siteid => $value ){
				$itemOne->removeSitelink( $siteid );
				$itemTwo->addSitelink( $siteid, $value['title'] );
			}

			break;
		}
	}

	if($itemOne->changed && $itemTwo->changed ){
		$itemOne->save();
		$itemTwo->save();
	}

}