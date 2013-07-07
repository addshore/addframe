<?php

/**
 * This file is main route into the framework
 * @author Addshore
 **/

//Set the include path
$IP = dirname(__FILE__) . '/';

//Include all files in /includes
foreach (glob("$IP/includes/*.php") as $filename){ include $filename; }

//Factory for creating sites
$Sites = new SiteFactory();