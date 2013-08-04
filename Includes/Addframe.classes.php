<?php

/**
 * @since 0.0.4
 * @author addshore
 */
return call_user_func( function() {

	$classes = array(
		'Addframe\AutoLoader' => 'AutoLoader.php',
		'Addframe\Coordinate' => 'Coordinate.php',
		'Addframe\Globals' => 'Globals.php',
		'Addframe\Http' => 'Http.php',
		'Addframe\Mysql' => 'Mysql.php',
		'Addframe\Registry' => 'Registry.php',
		'Addframe\Stathat' => 'Stathat.php',
	);

	return $classes;

} );
