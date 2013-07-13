<?php
/**
 * This file is an example use of various parts of the frame
 * @author Addshore
 **/

//Initiate the script
require_once( dirname(__FILE__).'/../init.php' );

//Create a site
$site['localhost'] = Globals::$Sites->newSite("localhost","127.0.0.1","/Mediawiki/api.php");
$site['localhost']->userlogin = new UserLogin('localhost','Bot','botp123');

//Login
$site['localhost']->dologin();