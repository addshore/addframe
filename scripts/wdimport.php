<?php
/**
 * This file is the main script used for moving information
 * from projects to wikidata
 * @author Addshore
 **/

//Initiate the script
require_once( dirname(__FILE__).'/../init.php' );

//Create a site
$wm = new Family('wikimedia',new UserLogin('addbot','password!'),'meta.wikimedia.org/w/api.php');
$wikidata = $wm->getFromMatrix('wikidatawiki');
$wikidata->doLogin();

//$dbConfig = parse_ini_file('~/replica.my.cnf');
//$db = new Mysql('tools-db','3306',$dbConfig['user'],$dbConfig['password'],$dbConfig['user'].'wikidata_p');
//unset($dbConfig);
//$dbQuery = $db->select('iwlink','*',null,array('ORDER BY' => 'updated ASC', 'LIMIT' => '100'));
//$rows = $db->mysql2array($dbQuery);
$rows = array(
	array('lang' => 'en','site' => 'wiki','namespace' => '0','title' => 'À Beira do Caminho'),
	array('lang' => 'en','site' => 'wiki','namespace' => '0','title' => 'Pear'),
	array('lang' => 'en','site' => 'wiki','namespace' => '0','title' => 'Banana'),
);
foreach($rows as $row){
	// Load our site
	$baseSite = $wm->getFromMatrix($row['lang'].$row['site']);
	$baseSite->doLogin();

	// Find the entity we want to work with
	$basePage = $baseSite->getPage($baseSite->getNamespace($row['namespace']).$row['title']);
	$basePage->load();
	$pageInterwikis = $basePage->getInterwikisFromtext();
	$baseEntity = $baseSite->getEntityFromPage($baseSite->dbname,$basePage->title);
	if( !isset($baseEntity->id) ){
		foreach($pageInterwikis as $interwikiData){
			$remoteSite = $wm->getFromMatrix($interwikiData['site'].$row['site']);
			$remoteEntity = $remoteSite->getEntityFromPage($interwikiData['link']);
			if( isset($remoteEntity->id) ){
				$baseEntity = $remoteEntity;
				break 1;
			}
		}
	}
	if( !isset($baseEntity->id) ){
		//look for entities for links to other sites! wikipedia {{wikipedia}}, [[wikipedia:en:target]] wikivoyage
	}
	if( !isset($baseEntity->id) ){
		//then we are working on a new entity! (we currently don't have to do anything extra..)
	}

	// Add everything we can to the entity
	foreach($pageInterwikis as $interwikiData){
		//@todo need a way of converting the match of en:pagename to enwiki:pagename
		//$baseEntity->addSitelink()
		//if wikipedia
			//add label
			//add aliases from redirects?
	}

	//Look for links to other sites! wikipedia {{wikipedia}}, [[wikipedia:en:target]] wikivoyage
		//if found, add them
		//if wikipedia
			//add label
			//add aliases from redirects?
	//save item
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
