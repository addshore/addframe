<?php

return call_user_func( function() {

	//todo the test classes should only be included when running tests (needs a separate entrance file)

	$classes = array(

		//base stuff
		'Addframe\Http' => 'Http.php',
		'Addframe\Logger' => 'Logger.php',
		'Addframe\GitInfo' => 'GitInfo.php',
		'Addframe\ConfigParser' => 'ConfigParser.php',

		//core
		'Addframe\Mediawiki\Site' => 'Mediawiki/Site.php',
		'Addframe\Mediawiki\SiteList' => 'Mediawiki/SiteList.php',
		'Addframe\Mediawiki\User' => 'Mediawiki/User.php',
		'Addframe\Mediawiki\Page' => 'Mediawiki/Page.php',
		'Addframe\Mediawiki\Revision' => 'Mediawiki/Revision.php',
		'Addframe\Mediawiki\Revisions' => 'Mediawiki/Revisions.php',

		//core api
		'Addframe\Mediawiki\Api' => 'Mediawiki/Api.php',
		'Addframe\Mediawiki\Api\UsageException' => 'Mediawiki/Api/UsageException.php',
		'Addframe\Mediawiki\Api\Request' => 'Mediawiki/Api/Request.php',
		'Addframe\Mediawiki\Api\EditRequest' => 'Mediawiki/Api/EditRequest.php',
		'Addframe\Mediawiki\Api\LoginRequest' => 'Mediawiki/Api/LoginRequest.php',
		'Addframe\Mediawiki\Api\LogoutRequest' => 'Mediawiki/Api/LogoutRequest.php',
		'Addframe\Mediawiki\Api\QueryRequest' => 'Mediawiki/Api/QueryRequest.php',
		'Addframe\Mediawiki\Api\SiteinfoRequest' => 'Mediawiki/Api/SiteinfoRequest.php',
		'Addframe\Mediawiki\Api\TokensRequest' => 'Mediawiki/Api/TokensRequest.php',
		'Addframe\Mediawiki\Api\RevisionsRequest' => 'Mediawiki/Api/RevisionsRequest.php',
		'Addframe\Mediawiki\Api\UserinfoRequest' => 'Mediawiki/Api/UserinfoRequest.php',
		'Addframe\Mediawiki\Api\UsersRequest' => 'Mediawiki/Api/UsersRequest.php',
		'Addframe\Mediawiki\Api\InfoRequest' => 'Mediawiki/Api/InfoRequest.php',

		//SiteMatrix
		'Addframe\Mediawiki\Family' => 'Mediawiki/Family.php',
		'Addframe\Mediawiki\Api\SitematrixRequest' => 'Mediawiki/Api/SitematrixRequest.php',

		//test
		'Addframe\TestHttp' => 'Http.php',
		'Addframe\Mediawiki\TestApi' => 'Mediawiki/Api.php',

	);

	return $classes;

} );
