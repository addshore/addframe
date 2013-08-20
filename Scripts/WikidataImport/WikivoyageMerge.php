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

$logpage = $wikidata->newPageFromTitle( 'User:Addbot/WYMerge' );
$ids = array();

//@todo the logic below should be somewhere else
$request = array(
	'action' => 'query', 'list' => 'usercontribs',
	'ucuser' => 'WYImporterBot', 'ucprop' => 'title',
	'uclimit' => '500', 'uccontinue' => '');
while( true ){
	$result = $wikidata->doRequest( $request );
	foreach( $result['query']['usercontribs'] as $contrib ){
		$ids[] = $contrib['title'];
	}
	if( array_key_exists( 'query-continue', $result ) ){
		$request['ucstart'] = $result['query-continue']['usercontribs']['ucstart'];
	} else {
		break;
	}
	echo count($ids)."\n";
}

echo count($ids)." in total...\n";

foreach ( $ids as $item ) {
	echo $item."\n";

	$itemOne = Entity::newFromId( $wikidata, $item );
	$itemOne->load();
	if( $itemOne->isMissing() ){
		echo "Missing\n";
		continue;
	}

	$itemTwo = null;

	//$pageGuess = array();
	$pageGuess2 = array();
	foreach ( $itemOne->getLanguageData('sitelinks') as $siteid => $value ) {
		$site = $wm->getSiteFromSiteid( $siteid );
		$page = $site->newPageFromTitle( $value['title'] );

		if( $site->getType() == 'wikivoyage' ){
			//$pageGuess[] = $wm->getSiteFromSiteid( $site->getLanguage().'wiki' )->newPageFromTitle( $page->title );
			$pageGuess2 = array_merge( $pageGuess2, $page->getPagesFromInterprojectLinks() );
		}
	}

	/* @var $page Page */
	foreach( $pageGuess2 as $page ){
		echo $page->site->getId().':'.$page->title."\n";
	}

	/* @var $page Page */
	foreach ( $pageGuess2 as $page ) {
		$itemTwo = $page->getEntity();
		if ( $itemTwo instanceof Entity ) {
			if( $itemOne->getId() !== $itemTwo->getId() ){
				$logpage->getText( true );
				$logpage->wikiText->appendText( "\n* [[".$itemOne->getId().']] can be merged with [['.$itemTwo->getId()."]]" );
				$logpage->save( 'Adding possible merge between [['.$itemOne->getId().']] and [['.$itemTwo->getId().']] ', true );
				break;
			}
		}
	}
}