<?php
/**
 * This file is the main point of access for the framework
 * @author Addshore
 **/

require_once( '../init.php' );

//Add our settings
$wiki = new mediawiki("127.0.0.1","/mediawiki/api.php");
$me = new userlogin('bot','botp123');

//Do stuff
$wiki->dologin($me);
$wiki->doEdit($me->getUserPage()->title."/Sandbox","Some random text = ".rand(0,100),"This is a summary (minor edit)",true );