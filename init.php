<?php

//Set the include path
$IP = dirname(__FILE__) . '/';

//Include all files in /includes
foreach (glob("$IP/includes/*.php") as $filename){ include $filename; }

//Create the $Registry Object
$Registry = new Registry;
$Sites = new Registry;