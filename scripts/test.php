<?php
/**
 * This file is first 'test' script
 * @author Addshore
 **/

//Initiate the script
require_once( dirname(__FILE__).'/../init.php' );

//Create a site
$site['localhost'] = Globals::$Sites->newSite("localhost","127.0.0.1","/mediawiki/api.php");
$site['localhost']->userlogin = new userlogin('localhost','Bot','botp123');

//Login
$site['localhost']->dologin();
//Play
$playpage = $site['localhost']->getPage($site['localhost']->userlogin->getUserPage()->title."/Sandbox");
$site['localhost']->doEdit(
	$playpage->title,
	"Some random text = ".rand(0,100),
	"This is a summary (minor edit)",
	true
);
echo $playpage->getText();