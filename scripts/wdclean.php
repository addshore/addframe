<?php
/**
 * This file is the main script used for cleaning up wikidata items
 * @author Addshore
 **/

//Initiate the script
require_once( dirname(__FILE__).'/../init.php' );

//Create a site
$wm = new Family('wikimedia',new UserLogin('localhost','Bot','botp123'),'meta.wikimedia.org/w/api.php');
$wikidata = $wm->getFromMatrix('wikidatawiki');
$wikidata->doLogin();

for($i = 1; $i < 14000000; $i ++){
	$entity = $wikidata->getEntityFromId('q'.$i);
	$entity->load();
	foreach($entity->languageData as $site => $link){
		$site = $wm->getFromMatrix($site);
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