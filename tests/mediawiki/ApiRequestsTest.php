<?php

use Addframe\Mediawiki\Api;
use Addframe\Mediawiki\LogoutRequest;
use Addframe\Mediawiki\TokensRequest;
use Addframe\TestHttp;

class ApiRequestsTest extends PHPUnit_Framework_TestCase{

	function testRequestLogout( ){
		$expected = '[]';
		$http = new TestHttp( $expected );
		$api = new Api( $http );
		$result = $api->doRequest( new LogoutRequest() );
		$this->assertEquals( json_decode( $expected, false ), $result );
	}

	function testRequestToken( ){
		$expected = '{"tokens":{"edittoken":"+\\"}}';
		$http = new TestHttp( $expected );
		$api = new Api( $http );
		$result = $api->doRequest( new TokensRequest() );
		$this->assertEquals( json_decode( $expected, false ), $result );
	}

}