<?php

use Addframe\Mediawiki\Api;
use Addframe\Mediawiki\ApiRequest;
use Addframe\TestHttp;

class ApiTest extends PHPUnit_Framework_TestCase{

	function testCanConstruct( ){
		$api = new Api( );
		$this->assertInstanceOf( 'Addframe\Mediawiki\Api', $api );
	}

	/**
	 * @dataProvider provideUrls
	 */
	function testCanGetNewFromUrl( $url ){
		$api = Api::newFromUrl( $url );
		$api->setUrl( $url );
		$this->assertEquals( $url, $api->getUrl() );
	}

	/**
	 * @dataProvider provideUrls
	 */
	function testCanSetUrl( $url ){
		$api = new Api();
		$api->setUrl( $url );
		$this->assertEquals( $url, $api->getUrl() );
	}

	function provideUrls(){
		return array(
			array( 'localhost/mediawiki/api.php' ),
			array( '127.0.0.1/api.php' ),
			array( 'en.wikipedia.org/wiki/api.php' ),
		);
	}

	/**
	 * @dataProvider provideApiRequests
	 */
	function testCanDoRequest( $request, $expected = 'a:0:{}' ){

		$api = new Api( new TestHttp( $expected ) );

		$api->setUrl( 'hostname' );
		$result = $api->doRequest( $request );

		$this->assertTrue( is_array( $result ) );
		$this->assertEquals( $expected, serialize( $result ) );
	}

	function provideApiRequests(){
		return array(
			//data, //post
			array( new ApiRequest(), serialize( array( 'key' => 'value', 'key2' => array( 'foo', 'bar' ) ) ) ),
			array( new ApiRequest( array( 'param' => 'value' ) ), serialize( array( 'key' => 'value' ) ) ),
			array( new ApiRequest( array( 'param' => 'value' ), true ) ),
			array( new ApiRequest( array( 'param' => 'value' ), false, 'php', true ) ),
			array( new ApiRequest( array( 'param' => 'value', 'param2' => 'value2' ), false, 'php', true ) ),
		);
	}

}