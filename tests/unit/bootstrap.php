<?php

/**
 * Include the main bootstrap
 */
require_once( __DIR__ . '/../bootstrap.php' );

/**
 * And classes that are only used in testing
 * todo make an autoloader here so we dont have to specify everything
 */
require_once( __DIR__ . '/Mediawiki/MediawikiTestCase.php' );