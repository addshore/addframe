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
$playentity = $site['localhost']->getEntity('q24');
$playentity->getEntity();
echo $playentity->buildEntity()."\n";
$playentity->modifyLabel('en-gb','New Label! :D');
echo $playentity->buildEntity();
$playentity->saveEntity();