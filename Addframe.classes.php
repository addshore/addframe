<?php

return call_user_func( function() {

	//todo the test classes should only be included when running tests (needs a separate entrance file)

	$classes = array(
		'Addframe\Http' => 'Http.php',
		'Addframe\Cache' => 'Cache.php',
		'Addframe\Cacheable' => 'Cacheable.php',
		'Addframe\Logger' => 'Logger.php',
		'Addframe\GitInfo' => 'GitInfo.php',

		'Addframe\Mediawiki\Site' => 'mediawiki/Site.php',
		'Addframe\Mediawiki\SiteList' => 'mediawiki/SiteList.php',

		'Addframe\Mediawiki\Api' => 'mediawiki/Api.php',
		'Addframe\Mediawiki\Api\Request' => 'mediawiki/api/Request.php',
		'Addframe\Mediawiki\Api\EditRequest' => 'mediawiki/api/EditRequest.php',
		'Addframe\Mediawiki\Api\LoginRequest' => 'mediawiki/api/LoginRequest.php',
		'Addframe\Mediawiki\Api\LogoutRequest' => 'mediawiki/api/LogoutRequest.php',
		'Addframe\Mediawiki\Api\QueryRequest' => 'mediawiki/api/QueryRequest.php',
		'Addframe\Mediawiki\Api\SiteinfoRequest' => 'mediawiki/api/SiteinfoRequest.php',
		'Addframe\Mediawiki\Api\TokensRequest' => 'mediawiki/api/TokensRequest.php',

		'Addframe\TestHttp' => 'Http.php',
		'Addframe\Mediawiki\TestApi' => 'mediawiki/Api.php',
	);

	return $classes;

} );
