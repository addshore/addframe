<?php

use Addframe\Mediawiki\Api;
use Addframe\Mediawiki\TokensRequest;
use Addframe\TestHttp;

class ApiRequestsTest extends PHPUnit_Framework_TestCase{

	function testRequestToken( ){
		$expected = '{"tokens":{"edittoken":"+\\"}}';
		$http = new TestHttp( $expected );
		$api = new Api( $http );
		$result = $api->doRequest( new TokensRequest() );
		$this->assertEquals( json_decode( $expected, false ), $result );
	}

}