<?php

//Set the include path
$IP = dirname(__FILE__) . '/';

//Include all files in /includes
foreach (glob("$IP/includes/*.php") as $filename){ include $filename; }