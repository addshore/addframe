<?php

namespace Addframe;

/**
 * This file is main route into the framework
 * @author Addshore
 **/

require_once(dirname( __FILE__ ) . '/Includes/AutoLoader.php');

AutoLoader::registerDirectory( dirname( __FILE__ ).'/Includes' );

Config::loadConfigs();
