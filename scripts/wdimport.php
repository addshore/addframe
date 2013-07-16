<?php
/**
 * This file is the main script used for moving information
 * from projects to wikidata
 * @author Addshore
 **/

//Initiate the script
require_once( dirname(__FILE__).'/../init.php' );

//Create a site
$wm = new Family('wikimedia',new UserLogin('addbot','password'),'meta.wikimedia.org');
$wikidata = $wm->getFromSiteid('wikidatawiki');
$wikidata->doLogin();

//$dbConfig = parse_ini_file('~/replica.my.cnf');
//$db = new Mysql('tools-db','3306',$dbConfig['user'],$dbConfig['password'],$dbConfig['user'].'wikidata_p');
//unset($dbConfig);
//$dbQuery = $db->select('iwlink','*',null,array('ORDER BY' => 'updated ASC', 'LIMIT' => '100'));
//$rows = $db->mysql2array($dbQuery);
$rows = array(
	//array('lang' => 'en','site' => 'wiki','namespace' => '0','title' => 'À Beira do Caminho'),
	array('lang' => 'en','site' => 'wikivoyage','namespace' => '0','title' => 'Berlin'),
	array('lang' => 'en','site' => 'wiki','namespace' => '0','title' => 'Pear'),
	array('lang' => 'en','site' => 'wiki','namespace' => '0','title' => 'Banana'),
);
foreach($rows as $row){
	// Load our site
	$baseSite = $wm->getFromSiteid($row['lang'].$row['site']);
	$baseSite->doLogin();

	// Find the entity we want to work with. First try the page we have, then interwiki links.
	$basePage = $baseSite->getPage($baseSite->getNamespace($row['namespace']).$row['title']);
	$basePage->load();
	$pageInterwikis = $basePage->getInterwikisFromtext();
	$baseEntity = $basePage->getEntity();
	if( !isset($baseEntity->id) ){
		echo "Failed to get entity from page, Looking at interwiki links..\n";
		foreach($pageInterwikis as $interwikiData){
			$remoteSite = $wm->getFromSiteid($interwikiData['site'].$baseSite->code);
			if($remoteSite instanceof Mediawiki){
				$remotePage = $remoteSite->getPage($interwikiData['link']);
				$remoteEntity = $remotePage->getEntity();
				if( isset($remoteEntity->id) ){
					echo "Found baseEntity from ".$remoteSite->wikiid." ".$remotePage->title." ".$remoteEntity->id."\n";
					$baseEntity = $remoteEntity;
					break 1;
				}
			}
		}
	}
	if( !isset($baseEntity->id) ){
		echo "Failed to get entiy from iwlinks, Looking at possible templates\n";
		//look for entities for links to other sites! wikipedia {{wikipedia}}, [[wikipedia:en:target]] wikivoyage
		///\n\[\[(WikiPedia):LANG:([^\]]+)\]\]/i
		///\n\{\{(WikiPedia)\|([^\]]+)\}\}/i
		//first do this for this page, if we still havn't found it look through the iw links
	}
	if( !isset($baseEntity->id) ){
		echo "We could not link the page to an entity\n";
		//for now we will move on, at some point we can start creating new entities
		continue;
	}
	echo "Found entity ".$baseEntity->id."\n";

	// Add everything we can to the entity and save
	foreach($pageInterwikis as $interwikiData){
		echo "Adding sitelink ".$interwikiData['site'].$baseSite->code.":".$interwikiData['link']."\n";
		$baseEntity->addSitelink($interwikiData['site'].$baseSite->code,$interwikiData['link']);
		if($wm->getFromSiteid($interwikiData['site'].$baseSite->code)->code == 'wiki'){
			//add label
			//add aliases from redirects
		}
	}

	if ($baseSite->code == 'wikivoyage'){
		//look for links to wikipedia via {{wikipedia}} and [[wikipedia:en:target]] [[WikiPedia:Berlin]]
		//if found add them
			//as we are now looking at a wikipedia article, try to add labels and aliases etc
	} else if ($baseSite->code == 'wiki'){
		//look for links to wikivoyage and add thme (no idea how these are formatted
	}

	//@todo only save if there is a change?
	$baseEntity->save();

	//Now see if we can update the pages

	//reload item from wikidata
	//foreach sitelink
		//if the sitelink also exists in the db as a page
		//load the page
		//page = cleanwikipage($page, $item)
		//save
	//match remaining links
	//if 0 remove from db, if > 0 update db
}

//cleanwikipage() is...
	//try to remove every sitelink in item from page
	//match interwikis
		//if 0 interwikis remove the iw comment
