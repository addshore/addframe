<?php

namespace Addframe\Test\Unit;

use Addframe\Mediawiki\Api;
use Addframe\Mediawiki\Api\Request;
use Addframe\TestHttp;

/**
 * Class ApiTest
 * @covers Addframe\Mediawiki\Api
 */
class ApiTest extends MediawikiTestCase{

	public function testCanConstruct( ){
		$api = new Api( );
		$this->assertInstanceOf( 'Addframe\Mediawiki\Api', $api );
	}

	/**
	 * @dataProvider provideUrls
	 */
	public function testCanGetNewFromUrl( $url ){
		$api = Api::newFromUrl( $url );
		$api->setUrl( $url );
		$this->assertEquals( $url, $api->getUrl() );
	}

	/**
	 * @dataProvider provideUrls
	 */
	public function testCanSetUrl( $url ){
		$api = new Api();
		$api->setUrl( $url );
		$this->assertEquals( $url, $api->getUrl() );
	}

	public function provideUrls(){
		return array(
			array( 'localhost/mediawiki/api.php' ),
			array( '127.0.0.1/api.php' ),
			array( 'en.wikipedia.org/wiki/api.php' ),
		);
	}

	/**
	 * @dataProvider provideApiRequests
	 */
	public function testCanDoRequest( $request, $expected = '[]' ){
		$http = new TestHttp( $expected );
		$api = new Api( $http );
		$result = $api->doRequest( $request );
		$this->assertEquals( json_decode( $expected, true ), $result );
	}

	/**
	 * @dataProvider provideApiRequests
	 */
	public function testCanDoRequestWithToken( Request $request, $expected = '[]' ){
		$http = new TestHttp( array( $this->getTestApiData( 'tokens/anonedittoken.json' ) , $expected ) );
		$api = new Api( $http );

		$this->assertArrayNotHasKey( 'token', $request->getParameters() );

		$result = $api->doRequestWithToken( $request, 'edit' );
		$this->assertArrayHasKey( 'token', $request->getParameters() );
		$this->assertEquals( json_decode( $expected, true ), $result );
	}

	/**
	 * @dataProvider provideApiRequests
	 */
	//todo decide when we should handle api errors of all kinds..
	public function testDoRequestWithTokenHandlesBadTokenThrowsException( Request $request ){
		$this->setExpectedException( '\Addframe\Mediawiki\Api\UsageException' );
		$http = new TestHttp( array( $this->getTestApiData( 'tokens/anonedittoken.json' ) , $this->getTestApiData( 'errors/badtoken.json' ) ) );
		$api = new Api( $http );

		$this->assertArrayNotHasKey( 'token', $request->getParameters() );
		$api->doRequestWithToken( $request, 'edit' );
	}

	/**
	 * @dataProvider provideApiRequests
	 */
	public function testDoRequestWithTokenPassesOtherExceptions( Request $request ){
		$this->setExpectedException( '\Addframe\Mediawiki\Api\UsageException' );
		$http = new TestHttp( array( $this->getTestApiData( 'errors/missingparam.json' ) ) );
		$api = new Api( $http );
		$this->assertArrayNotHasKey( 'token', $request->getParameters() );
		$api->doRequestWithToken( $request, 'edit' );
	}

	/**
	 * There is no real reason to provide all possible requests here as they all extend Request
	 * Generally if the first few succeed so will the rest..
	 * todo we could have a list of possible api requests somewhere and iterate over them all with their default values
	 **/
	public function provideApiRequests(){
		return array(
			array( new Request() ),
			array( new Request( array ( 'param' => 'value' ) ) ),
			array( new Request( array ( 'param' => 'value', 'format' => 'php' ) ) ),
			array( new Request( array ( 'param' => 'value', 'param2' => 'value2' ), true ) ),
			array( new Request(), '[]' ),
			array( new Request(), $this->getTestApiData( 'tokens/watchtoken.json' ) ),
		);
	}

	public function provideErrorCodeFiles(){
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
	public function testApiExceptions( $file ){
		$this->setExpectedException( '\Addframe\Mediawiki\Api\UsageException' );
		$http = new TestHttp( $this->getTestApiData( "errors/{$file}" ) );
		$api = new Api( $http );
		$api->doRequest( new Request() );
	}

}