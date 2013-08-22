<?php

use Addframe\Mediawiki\Api;

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
	 * @dataProvider provideRequestData
	 */
	function testCanDoRequest( $data, $post ){
		$expected = serialize( array( 'returnKey' => array( 'subKey' => 'return Value' ) ) );
		$api = new Api( $this->getMockHttp( array( 0 => $expected ) ) );

		$api->setUrl( 'hostname' );
		$result = $api->doRequest( $data, $post );

		$this->assertTrue( is_array( $result ) );
		$this->assertEquals( $expected, serialize( $result ) );
	}

	function provideRequestData(){
		return array(
			//data, //post
			array( array( 'key' => 'value' ), null ),
			array( array( 'key' => 'value' ), array( 'key' => 'value' ) ),
			array( array(), array( 'key' => 'value' ) ),
		);
	}

	function getMockHttp( $requestResult = array( 0 => '' ) ){
		$http = $this->getMock( 'Addframe\Http', array('get','post') );
		foreach( $requestResult as $key => $return ){
			$http->expects( $this->at( $key ) )->method( 'get' )->will( $this->returnValue( $return ) );
			$http->expects( $this->at( $key ) )->method( 'post' )->will( $this->returnValue( $return ) );
		}
		return $http;
	}

}