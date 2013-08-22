<?php

return call_user_func( function() {

	$classes = array(
		'Addframe\Http' => 'Http.php',

		'Addframe\Mediawiki\Site' => 'Site.php',
		'Addframe\Mediawiki\SiteList' => 'SiteList.php',
	);

	return $classes;

} );
