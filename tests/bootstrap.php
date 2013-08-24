<?php

/**
 * Include the entrance file
 */
require_once( __DIR__.'/../Addframe.php' );

/**
 * Over ride the default cache prefix
 * This makes sure none of our test data messes up any real data
 */
\Addframe\Cache::$prefix = 't_';