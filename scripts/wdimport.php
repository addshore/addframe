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
	$page = $site->getPage($site->getNamespace($row['namespace']).$row['title']);
	$page->getText();

}
	//match remaining interwiki links
	//find item for the article
		//if no item..
			//foreach interwikilink
				//find item
	//if still no item create a new item
	//foreach interwikilink
		//add sitelink to item
		//if sitelink successfull
			//add label
			//add aliases from redirects to the page???
	//save item
	//reload item from wikidata
	//foreach sitelink
		//if the sitelink also exists in the db as a page
			//load the page
				//page = cleanwikipage($page, $item)
				//save
				//match remaining links
				//if 0 remove from db, if > 0 update db


//cleanwikipage() is...
	//try to remove every sitelink in item from page
	//match interwikis
		//if 0 interwikis remove the iw comment
