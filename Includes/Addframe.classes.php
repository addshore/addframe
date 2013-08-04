<?php

/**
 * @since 0.0.4
 * @author addshore
 */
return call_user_func( function() {

	$classes = array(
		'Addframe\AutoLoader' => 'Addframe/AutoLoader.php',
		'Addframe\Coordinate' => 'Addframe/Coordinate.php',
		'Addframe\Globals' => 'Addframe/Globals.php',
		'Addframe\Http' => 'Addframe/Http.php',
		'Addframe\Mysql' => 'Addframe/Mysql.php',
		'Addframe\Registry' => 'Addframe/Registry.php',
		'Addframe\Stathat' => 'Addframe/Stathat.php',
	);

	return $classes;

} );
