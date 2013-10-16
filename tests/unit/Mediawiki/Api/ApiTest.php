<?php

namespace Addframe\Test\Unit;

use Addframe\Cache;
use Addframe\Mediawiki\Api;
use Addframe\Mediawiki\Api\Request;
use Addframe\TestHttp;

/**
 * Class ApiTest
 * @covers Addframe\Mediawiki\Api
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
	function testCanDoRequest( $request, $expected = '[]' ){
		$http = new TestHttp( $expected );
		$api = new Api( $http );
		$result = $api->doRequest( $request, false );
		$this->assertEquals( json_decode( $expected, true ), $result );
	}

	/**
	 * @dataProvider provideApiRequests
	 */
	function testCanDoRequestWithToken( Request $request, $expected = '[]' ){
		$http = new TestHttp( array( $this->getTestApiData( 'tokens/anonedittoken.json' ) , $expected ) );
		$api = new Api( $http );

		$this->assertArrayNotHasKey( 'token', $request->getParameters() );

		$result = $api->doRequestWithToken( $request, 'edittoken' , false );
		$this->assertArrayHasKey( 'token', $request->getParameters() );
		$this->assertEquals( json_decode( $expected, true ), $result );
	}

	/**
	 * @dataProvider provideApiRequests
	 * NOTE: We don't actually receive an exception, just null (the exception is handled before the result is passed back)
	 */
	function testDoRequestWithTokenHandlesBadToken( Request $request ){
		$http = new TestHttp( array( $this->getTestApiData( 'tokens/anonedittoken.json' ) , $this->getTestApiData( 'errors/badtoken.json' ) ) );
		$api = new Api( $http );

		$this->assertArrayNotHasKey( 'token', $request->getParameters() );
		$result = $api->doRequestWithToken( $request, 'edittoken' , false );
		$this->assertNull( $result );
	}

	/**
	 * @dataProvider provideApiRequests
	 */
	function testDoRequestWithTokenPassesOtherExceptions( Request $request ){
		$this->setExpectedException( '\Addframe\Mediawiki\Api\UsageException' );
		$http = new TestHttp( array( $this->getTestApiData( 'errors/missingparam.json' ) ) );
		$api = new Api( $http );
		$this->assertArrayNotHasKey( 'token', $request->getParameters() );
		$api->doRequestWithToken( $request, 'edittoken' , false );
	}

	/**
	 * There is no real reason to provide all possible requests here as they all extend Request
	 * Generally if the first few succeed so will the rest..
	 * todo we could have a list of possible api requests somewhere and iterate over them all with their default values
	 **/
	function provideApiRequests(){
		return array(
			array( new Request() ),
			array( new Request( array( 'param' => 'value' ) ) ),
			array( new Request( array( 'param' => 'value', 'format' => 'php' ) ) ),
			array( new Request( array( 'param' => 'value', 'param2' => 'value2' ), true ) ),
			array( new Request(), '[]' ),
			array( new Request(), $this->getTestApiData( 'tokens/watchtoken.json' ) ),
		);
	}

	function provideErrorCodeFiles(){
		$return = array();
		foreach( scandir( __DIR__ . '/data/errors' ) as $file ){
			if( substr( $file, -5 ) === '.json' )
			$return[] = array( $file );
		}
		return $return;
	}

	/**
	 * @dataProvider provideErrorCodeFiles
	 */
	function testApiExceptions( $file ){
		$this->setExpectedException( '\Addframe\Mediawiki\Api\UsageException' );
		$http = new TestHttp( $this->getTestApiData( "errors/{$file}" ) );
		$api = new Api( $http );
		$api->doRequest( new Request() );
	}

	function testCachedResultCanBeIgnored(){
		$request = new Request( array( 'test' => 'testGettingCachedResultWorks' ), false, 100 );
		Cache::remove( $request ); //remove if it is already there

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
		$request = new Request( array( 'test' => 'testCachedResultExpires' ), false, 2 );
		Cache::remove( $request ); //remove if it is already there

		//do our first request to get some data and cache it
		$api = new Api( new TestHttp( json_encode( array( 'RESULT 1' ) ) ) );
		$api->doRequest( $request );
		$this->assertEquals( array( 'RESULT 1' ) , $request->getResult() );

		//change the result the api would provide, we will still get the cached data
		$api = new Api( new TestHttp( json_encode( array( 'NEW RESULT' ) ) ) );
		$api->doRequest( $request );
		$this->assertEquals( array( 'RESULT 1' ) , $request->getResult() );

		//sleep until the cache expires
		while( Cache::age( $request ) < $request->maxCacheAge() ){
			sleep(1);
		}

		//we should now get the new data!
		$api->doRequest( $request );
		$this->assertEquals( array( 'NEW RESULT' ) , $request->getResult() );
	}

}