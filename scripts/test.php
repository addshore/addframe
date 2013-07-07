<?php
/**
 * This file is first 'test' script
 * @author Addshore
 **/

//Initiate the script
require_once( dirname(__FILE__).'/../init.php' );

//Create a site
$site['localhost'] = Globals::$Sites->newSite("localhost","127.0.0.1","/Mediawiki/api.php");
$site['localhost']->userlogin = new UserLogin('localhost','Bot','botp123');

//Login
$site['localhost']->dologin();
//Play
$playpage = $site['localhost']->getPage($site['localhost']->userlogin->getUserPage()->title."/Sandbox");
$playpage->getText();
$playpage->appendText(".");
$playpage->save("Save Summary",true);
$me = $site['localhost']->userlogin;
$me->getRights();
$playpage->getCategories();