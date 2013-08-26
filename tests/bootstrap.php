<?php

/**
 * Include the entrance file
 */
require_once( __DIR__.'/../Addframe.php' );
require_once( __DIR__.'/DefaultTestCase.php' );
require_once( __DIR__.'/mediawiki/MediawikiTestCase.php' );


/**
 * Turn default logging off
 * This makes sure we don't spoil logs we actually use..
 */
\Addframe\Logger::setDefaultSeverityThreshold( \Addframe\Logger::OFF );

/**
 * Over ride the default cache prefix
 * This makes sure none of our test data messes up any real data
 */
\Addframe\Cache::$prefix = 't_';