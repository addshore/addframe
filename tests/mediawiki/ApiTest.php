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
	function testCanDoRequest( $request, $expected = '' ){
		$http = new TestHttp( $expected );
		$api = new Api( $http );
		$result = $api->doRequest( $request, false );
		$this->assertEquals( json_decode( $expected, false ), $result );
	}

	/**
	 * There is no real reason to provide all possible requests here as they all extend ApiRequest
	 * Generally if the first few succeed so will the rest..
	 * todo we could have a list of possible api requests somewhere and iterate over them all with their default values
	 **/
	function provideApiRequests(){
		return array(
			array( new Addframe\Mediawiki\ApiRequest() ),
			array( new Addframe\Mediawiki\ApiRequest( array( 'param' => 'value' ) ) ),
			array( new Addframe\Mediawiki\ApiRequest( array( 'param' => 'value', 'format' => 'php' ) ) ),
			array( new Addframe\Mediawiki\ApiRequest( array( 'param' => 'value', 'param2' => 'value2' ) ) ),
			array( new Addframe\Mediawiki\LogoutRequest(), '[]' ),
			array( new Addframe\Mediawiki\TokensRequest(), '{"tokens":{"edittoken":"+\\"}}' ),
			array( new Addframe\Mediawiki\TokensRequest( 'watch' ), '{"tokens":{"watchtoken":"863bb60669575ac8619662ddad5fc2ac+\\"}}' ),
		);
	}

	function testCachedResultCanBeIgnored(){
		$request = new Addframe\Mediawiki\ApiRequest( array( 'test' => 'testGettingCachedResultWorks' ), false, 100 );
		\Addframe\Cache::remove( $request ); //remove if it is already there

		//do our first request to get some data and cache it
		$api = new Api( new TestHttp( json_encode( array( 'RESULT 1' ) ) ) );
		$api->doRequest( $request );
		$this->assertEquals( array( 'RESULT 1' ) , $request->getResult() );

		//change the result the api would provide, we will still get the cached data
		$api = new Api( new TestHttp( json_encode( array( 'NEW RESULT' ) ) ) );
		$api->doRequest( $request );
		$this->assertEquals( array( 'RESULT 1' ) , $request->getResult() );

		//force to ignore any cache and make sure we get the cached data
		$api->doRequest( $request, false );
		$this->assertEquals( array( 'NEW RESULT' ) , $request->getResult() );

		//make sure the cache now holds the new data
		$api->doRequest( $request );
		$this->assertEquals( array( 'NEW RESULT' ) , $request->getResult() );
	}

	function testCachedResultExpires(){
		$request = new Addframe\Mediawiki\ApiRequest( array( 'test' => 'testCachedResultExpires' ), false, 2 );
		\Addframe\Cache::remove( $request ); //remove if it is already there

		//do our first request to get some data and cache it
		$api = new Api( new TestHttp( json_encode( array( 'RESULT 1' ) ) ) );
		$api->doRequest( $request );
		$this->assertEquals( array( 'RESULT 1' ) , $request->getResult() );

		//change the result the api would provide, we will still get the cached data
		$api = new Api( new TestHttp( json_encode( array( 'NEW RESULT' ) ) ) );
		$api->doRequest( $request );
		$this->assertEquals( array( 'RESULT 1' ) , $request->getResult() );

		//sleep until the cache expires
		while( \Addframe\Cache::age( $request ) < $request->maxCacheAge() ){
			sleep(1);
		}

		//we should now get the new data!
		$api->doRequest( $request );
		$this->assertEquals( array( 'NEW RESULT' ) , $request->getResult() );
	}

}