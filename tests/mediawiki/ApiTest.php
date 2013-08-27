<?php

use Addframe\Mediawiki\Api;
use Addframe\Mediawiki\ApiRequest;
use Addframe\Mediawiki\TestApi;
use Addframe\TestHttp;

/**
 * Class ApiTest
 * @covers Addframe\Mediawiki\Api
 * @covers Addframe\Mediawiki\TestApi
 */

class ApiTest extends MediawikiTestCase{

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
		$this->assertEquals( json_decode( $expected, true ), $result );
	}

	/**
	 * @dataProvider provideApiRequests
	 */
	function testCanDoRequestWithToken( ApiRequest $request, $expected = '[]' ){
		$http = new TestHttp( array( $this->getData( 'tokens/anonedittoken.json' ) , $expected ) );
		$api = new Api( $http );

		$this->assertArrayNotHasKey( 'token', $request->getParameters() );

		$result = $api->doRequestWithToken( $request, 'edittoken' , false );
		$this->assertArrayHasKey( 'token', $request->getParameters() );
		$this->assertEquals( json_decode( $expected, true ), $result );
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
			array( new Addframe\Mediawiki\ApiRequest(), '[]' ),
			array( new Addframe\Mediawiki\ApiRequest(), $this->getData( 'tokens/watchtoken.json' ) ),
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

	function testTestApiWithArray(){
		$expected1 = array( 'testTestApi array1' );
		$expected2 = array( 'testTestApi array2' );
		$testApi = new TestApi( array( json_encode( $expected1 ), json_encode( $expected2 ) ) );
		$request = new Addframe\Mediawiki\ApiRequest();

		$result = $testApi->doRequest( $request, false );
		$this->assertEquals( $expected1, $result );
		$this->assertEquals( $expected1, $request->getResult() );

		$result = $testApi->doRequest( $request, false );
		$this->assertEquals( $expected2, $result );
		$this->assertEquals( $expected2, $request->getResult() );
	}

	function testTestApiWithString(){
		$expected = array( 'testTestApi string' );
		$testApi = new TestApi( json_encode( $expected ) );

		$request = new Addframe\Mediawiki\ApiRequest();
		$result = $testApi->doRequest( $request, false );
		$this->assertEquals( $expected, $result );
		$this->assertEquals( $expected, $request->getResult() );

		$result = $testApi->doRequest( $request, false );
		$this->assertEquals( $expected, $result );
		$this->assertEquals( $expected, $request->getResult() );
	}

}