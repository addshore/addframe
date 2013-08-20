<?php

namespace Addframe;

/**
 * This file is main route into the framework
 * @author Addshore
 **/

$IP = dirname( __FILE__ ) . '/Includes';
$CP = dirname( __FILE__ ) . '/Configs';
require_once("$IP/Addframe.init.php");
require_once("$IP/Irc/Irc.init.php");
require_once("$IP/Mediawiki/Mediawiki.init.php");
require_once("$IP/Mediawiki/Wikibase/Wikibase.init.php");
require_once( "$IP/KLogger/src/KLogger.php" );

Config::loadConfigs( $CP );
