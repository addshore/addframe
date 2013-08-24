<?php

return call_user_func( function() {

	//todo the test classes should only be included when running tests (needs a separate entrance file)

	$classes = array(
		'Addframe\Http' => 'Http.php',
		'Addframe\Cache' => 'Cache.php',
		'Addframe\Cacheable' => 'Cacheable.php',

		'Addframe\Mediawiki\Site' => 'mediawiki/Site.php',
		'Addframe\Mediawiki\Api' => 'mediawiki/Api.php',
		'Addframe\Mediawiki\ApiRequest' => 'mediawiki/ApiRequest.php',
		'Addframe\Mediawiki\SiteList' => 'mediawiki/SiteList.php',

		'Addframe\TestHttp' => 'Http.php',
		'Addframe\Mediawiki\TestApi' => 'mediawiki/Api.php',
	);

	return $classes;

} );
