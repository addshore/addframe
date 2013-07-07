<?php
/**
 * This file is the main point of access for the framework
 * @author Addshore
 **/

require_once( '../init.php' );

//Create a site
$Sites->localhost = new mediawiki("127.0.0.1","/mediawiki/api.php");
//Add the login info to the site
$Sites->localhost->userlogin = new userlogin('bot','botp123');

//Login
$Sites->localhost->dologin();
//Edit
$Sites->localhost->doEdit(
	$Sites->localhost->userlogin->getUserPage()->title."/Sandbox",
	"Some random text = ".rand(0,100),
	"This is a summary (minor edit)",
	true
);