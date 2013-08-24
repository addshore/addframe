<?php

use Addframe\Mediawiki\ApiRequest;
use Addframe\Mediawiki\TestApi;

class ApiRequestTest extends PHPUnit_Framework_TestCase{

	/**
	 * @dataProvider provideConstructionData
	 */
	function testCanConstruct( $params = array(), $shouldBePosted = false, $cache = false ){
		$request = new ApiRequest( $params, $shouldBePosted , $cache );
		$this->assertInstanceOf( 'Addframe\Mediawiki\ApiRequest', $request );

		//force our expected format param..
		if( !array_key_exists( 'format', $params ) ){
			$params['format'] = 'json';
		}

		//check the params
		$this->assertEquals( $params, $request->getParameters() );
		//check the defaults
		$this->assertEquals( $cache, $request->maxCacheAge() );
		$this->assertEquals( $shouldBePosted,  $request->isPost() );
		$this->assertEquals( null, $request->getResult() );
	}

	function provideConstructionData(){
		return array(
			//data, //post, //cacheable
			array( ),
			array( array() ),
			array( array( 'param' => 'value' ) ),
			array( array( 'param' => 'value' ), true ),
			array( array( 'param' => 'value' ), true ),
			array( array( 'param' => 'value', 'param2' => 'value2' ), false ),
		);
	}

	function testHash(){
		$request1 = new ApiRequest( array() );
		$request2 = new ApiRequest( array() );
		$this->assertEquals( $request1->getHash(), $request2->getHash() );
		$request1 = new ApiRequest( array( 'value' ) );
		$request2 = new ApiRequest( array() );
		$this->assertNotEquals( $request1->getHash(), $request2->getHash() );
		$request1 = new ApiRequest( array( 'key' => 'SomeLongValues?afg?2rq' ) );
		$request2 = new ApiRequest( array() );
		$this->assertNotEquals( $request1->getHash(), $request2->getHash() );
	}

}