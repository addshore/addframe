<?php

//Include all files in /includes
foreach (glob("includes/*.php") as $filename){ include $filename; }

//Add our settings
$wiki = new mediawiki("127.0.0.1","/mediawiki/api.php");
$me = new userlogin('bot','botp123');

//Do stuff
$wiki->dologin($me);
$wiki->doEdit($me->getUserPageTitle()."/Sandbox","Some random text = ".rand(0,100) );