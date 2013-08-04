<?php

/**
 * @since 0.0.4
 * @author addshore
 */
return call_user_func( function() {

	$classes = array(
		'Irc\Channel' => 'Irc/Channel.php',
		'Irc\Server' => 'Irc/Server.php',
	);

	return $classes;

} );
