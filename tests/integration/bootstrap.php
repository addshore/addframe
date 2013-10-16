<?php

/**
 * Include the main bootstrap
 */
require_once( __DIR__ . '/../bootstrap.php' );

/**
 * Over ride the default cache prefix
 * This makes sure none of our test data messes up any real data
 */
\Addframe\Cache::$prefix = 'it_';

/**
 * Define a site location to be used in integration tests
 */
define( "SITEURL", "http://localhost/wiki/index.php" );
define( "SITEUSER", "Test" );
define( "SITEPASS", "integration" );