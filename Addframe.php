<?php

spl_autoload_register( function ( $className ) {
	static $classes = false;

	if ( $classes === false ) {
		$classes = include( __DIR__ . '/' . 'Addframe.classes.php' );
	}

	if ( array_key_exists( $className, $classes ) ) {
		include_once __DIR__ . '/includes/' . $classes[$className];
	}
} );

//Include all core ApiRequests
//todo we should create a Mediawiki entry script that includes the line below
//todo it should also contain its own autoloader and class list (probably)
include_once( __DIR__.'/includes/mediawiki/ApiRequests.php' );