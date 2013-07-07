<?php
/**
 * This file is first 'test' script
 * @author Addshore
 **/

//Initiate the script
require_once( dirname(__FILE__).'/../init.php' );

//Create a site
$site['localhost'] = Globals::$Sites->newSite("localhost","127.0.0.1","/mediawiki/api.php");
//Add the login info to the site
$site['localhost']->userlogin = new userlogin('bot','botp123');

//Login
$site['localhost']->dologin();
//Edit
$site['localhost']->doEdit(
	$site['localhost']->userlogin->getUserPage()->title."/Sandbox",
	"Some random text = ".rand(0,100),
	"This is a summary (minor edit)",
	true
);