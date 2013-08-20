<?php

/**
 * @since 0.0.4
 * @author addshore
 */
return call_user_func( function() {

	$classes = array(
		'Addframe\Addframe' => 'Addframe.php',
		'Addframe\AutoLoader' => 'AutoLoader.php',
		'Addframe\Coordinate' => 'Coordinate.php',
		'Addframe\Config' => 'Config.php',
		'Addframe\Http' => 'Http.php',
		'Addframe\Mysql' => 'Mysql.php',
		'Addframe\Registry' => 'Registry.php',
		'Addframe\Stathat' => 'Stathat.php',
	);

	return $classes;

} );
