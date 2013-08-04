<?php

/**
 * @since 0.0.4
 * @author addshore
 */
return call_user_func( function() {

	$classes = array(
		'Addframe\Irc\Channel' => 'Channel.php',
		'Addframe\Irc\Server' => 'Server.php',
	);

	return $classes;

} );
