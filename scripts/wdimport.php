<?php
/**
 * This file is the main script used for moving information
 * from projects to wikidata
 * @author Addshore
 **/

//Initiate the script
require_once( dirname(__FILE__).'/../init.php' );

//Create a site
$wm = new Family('wikimedia',new UserLogin('localhost','Bot','botp123'),'meta.wikimedia.org/w/api.php');
$wikidata = $wm->addSiteFromMatrix('wikidatawiki');
$wikidata->doLogin();

$dbConfig = parse_ini_file('~/replica.my.cnf');
$db = new Mysql('tools-db','3306',$dbConfig['user'],$dbConfig['password'],$dbConfig['user'].'wikidata_p');
unset($dbConfig);
$dbQuery = $db->select('iwlink','*',null,array('ORDER BY' => 'updated ASC', 'LIMIT' => '100'));
$rows = $db->mysql2array($dbQuery);
foreach($rows as $row){
	$site = $wm->getSite($row['language'].$row['site']);
	$site->doLogin();

	//##--## Find the entity that we want to work with!
	$basePage = $site->getPage($site->getNamespace($row['namespace']).$row['title']);
	$basePage->load();
	$pageInterwikis = $basePage->getInterwikisFromtext();
	$baseEntity = $site->getEntityFromPage($site->dbname,$basePage->title);
	if( !isset($baseEntity->id) ){
		//@todo make this looking a bit recursive? (maybe a function)
		foreach($pageInterwikis as $interwikiData){
			//find item (if we do break out of the loop)!
		}
		//also look for links between projects here and thus entities
	}
	if( !isset($baseEntity->id) ){
		//then we are working on a new entity!
	}

	//##--## Add stuff to the entity!

	//First look for links to other sites! wikipedia {{wikipedia}}, [[wikipedia:en:target]] wikivoyage
		//if found, add them
		//if wikipedia
			//add label
			//add aliases from redirects?
	foreach($pageInterwikis as $interwikiData){
		//@todo need a way of converting the match of en:pagename to enwiki:pagename
		//$baseEntity->addSitelink()
		//if wikipedia
			//add label
			//add aliases from redirects?
	}
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
