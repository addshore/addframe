<?php

return call_user_func( function() {

	$classes = array(
		'Addframe\Http' => 'Http.php',

		'Addframe\Mediawiki\Site' => 'mediawiki/Site.php',
		'Addframe\Mediawiki\Api' => 'mediawiki/Api.php',
		'Addframe\Mediawiki\SiteList' => 'mediawiki/SiteList.php',
	);

	return $classes;

} );
