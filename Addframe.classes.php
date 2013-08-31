<?php

return call_user_func( function() {

	//todo the test classes should only be included when running tests (needs a separate entrance file)

	$classes = array(

		//base stuff
		'Addframe\Http' => 'Http.php',
		'Addframe\Cache' => 'Cache.php',
		'Addframe\Cacheable' => 'Cacheable.php',
		'Addframe\Logger' => 'Logger.php',
		'Addframe\GitInfo' => 'GitInfo.php',

		//core
		'Addframe\Mediawiki\Site' => 'mediawiki/Site.php',
		'Addframe\Mediawiki\SiteList' => 'mediawiki/SiteList.php',

		//core api
		'Addframe\Mediawiki\Api' => 'mediawiki/Api.php',
		'Addframe\Mediawiki\Api\UsageException' => 'mediawiki/api/UsageException.php',
		'Addframe\Mediawiki\Api\Request' => 'mediawiki/api/Request.php',
		'Addframe\Mediawiki\Api\EditRequest' => 'mediawiki/api/EditRequest.php',
		'Addframe\Mediawiki\Api\LoginRequest' => 'mediawiki/api/LoginRequest.php',
		'Addframe\Mediawiki\Api\LogoutRequest' => 'mediawiki/api/LogoutRequest.php',
		'Addframe\Mediawiki\Api\QueryRequest' => 'mediawiki/api/QueryRequest.php',
		'Addframe\Mediawiki\Api\SiteinfoRequest' => 'mediawiki/api/SiteinfoRequest.php',
		'Addframe\Mediawiki\Api\TokensRequest' => 'mediawiki/api/TokensRequest.php',
		'Addframe\Mediawiki\Api\RevisionsRequest' => 'mediawiki/api/RevisionsRequest.php',
		'Addframe\Mediawiki\Api\UserinfoRequest' => 'mediawiki/api/UserinfoRequest.php',

		//SiteMatrix
		'Addframe\Mediawiki\Family' => 'mediawiki/Family.php',
		'Addframe\Mediawiki\Api\SitematrixRequest' => 'mediawiki/api/SitematrixRequest.php',

		//test
		'Addframe\TestHttp' => 'Http.php',
		'Addframe\Mediawiki\TestApi' => 'mediawiki/Api.php',

	);

	return $classes;

} );
