<?php
/**
 * This file is the main script used for cleaning up wikidata items
 * @author Addshore
 **/
use Addframe\Mediawiki\Family;
use Addframe\Mediawiki\UserLogin;

/**
 * @author Addshore
 */

require_once( dirname( __FILE__ ) . '/../Init.php' );

//Create a site
$wm = new Family( new UserLogin( 'Bot', 'botp123' ), 'meta.wikimedia.org/w/api.php' );
$wikidata = $wm->getSiteFromSiteid( 'wikidatawiki' );
$wikidata->requestLogin();

for ( $i = 1; $i < 14000000; $i ++ ) {
	$entity = $wikidata->newEntityFromEntityId( 'q' . $i );
	$entity->load();
	foreach ( $entity->getLanguageData() as $site => $link ) {
		$site = $wm->getSiteFromSiteid( $site );
		//if the article does not exist
		//if we have been moved update the sitelink
		//if not then remove the sitelink
		//if we are a redirect, find out target and update the sitelink
		//Normalise the namespace is possible
		//if no label, add it from the title
		//if redirects go to here add their names as aliases
	}
	//if stuff has changed on the entity then save it!
}