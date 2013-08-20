<?php
/**
 * This file is the main script used for cleaning up wikidata items
 * @author Addshore
 **/
use Addframe\Mediawiki\Family;
use Addframe\Mediawiki\Page;
use Addframe\Mediawiki\UserLogin;
use Addframe\Config;

/**
 * @author Addshore
 */

require_once( dirname( __FILE__ ) . '/../Init.php' );

//Create a site
$wm = new Family(
	new UserLogin( Config::get( 'wikiuser', 'username'),
		Config::get( 'wikiuser', 'password') ), Config::get( 'wikiuser', 'home') );
$wikidata = $wm->getSiteFromSiteid( 'wikidatawiki' );
$wikidata->login();

//@todo different item generations here
$list = array();

foreach ( $list as $itemId ) {
	$entity = $wikidata->newEntityFromEntityId( $itemId );
	$entity->load();
	foreach ( $entity->getLanguageData() as $siteId => $articleTitle ) {
		$page = new Page( $wm->getSiteFromSiteid( $siteId ), $articleTitle );
		$docPage = getDocsubpage( $siteId );
		//@todo It might be nice to have a $page->findBase()
		if( isset( $docPage ) && preg_match('/\/'.$docPage.'$/', $page->title->getTitle() ) ){
			$entity->removeSitelink( $siteId );
			$entity->addSitelink( $siteId, preg_replace( '/\/'.$docPage.'$/', '' , $page->title->getTitle() ) );
		}
	}
	if( $entity->isChanged() ){
		$entity->save();
	}
}

/**
 * @param $site string siteid
 * @return null|string name of doc page if set
 * @author SchreyP (Listed origional 61 subpages)
 * @author Addshore
 * @todo this should probably be included in the framework somewhere..
 */
function getDocsubpage( $site ){
	
	$docPages = array('angwiki' => 'doc','astwiki' => 'doc','aswiki' => 'doc','azwiki' => 'doc','bnwiki' => 'doc','cywiki' => 'doc','enwiki' => 'doc',
		'eswiki' => 'doc','etwiki' => 'doc','gawiki' => 'doc','gdwiki' => 'doc','guwiki' => 'doc','hiwiki' => 'doc','hywiki' => 'doc','idwiki' => 'doc',
		'ilowiki' => 'doc','jawiki' => 'doc','jvwiki' => 'doc','kmwiki' => 'doc','knwiki' => 'doc','kywiki' => 'doc','lawiki' => 'doc','minwiki' => 'doc',
		'mkwiki' => 'doc','mrwiki' => 'doc','mswiki' => 'doc','newiki' => 'doc','nsowiki' => 'doc','orwiki' => 'doc','pawiki' => 'doc','ptwiki' => 'doc',
		'rowiki' => 'doc','ruwiki' => 'doc','sawiki' => 'doc','scowiki' => 'doc','shwiki' => 'doc','simplewiki' => 'doc','siwiki' => 'doc',
		'swwiki' => 'doc','thwiki' => 'doc','tpiwiki' => 'doc','uzwiki' => 'doc','viwiki' => 'doc','zhwiki' => 'doc');
	$docPages['frwiki'] = 'Documentation';
	$docPages['cswiki'] = 'ús';
	$docPages['elwiki'] = 'τεκμηρίωση';
	$docPages['glwiki'] = 'uso';
	$docPages['itwiki'] = 'man';
	$docPages['kawiki'] = 'ინფო';
	$docPages['kowiki'] = '설명문서';
	$docPages['nowiki'] = 'dok';
	$docPages['slwiki'] = 'dok';
	$docPages['trwiki'] = 'belge';
	$docPages['ukwiki'] = 'Документація';
	$docPages['zh-yuewiki'] = '解';
	
	if( array_key_exists( $site, $docPages ) ){
		return $docPages[$site];
	}
	return null;
}