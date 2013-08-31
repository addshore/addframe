<?php

use Addframe\Mediawiki\Api\Request;

/**
 * Class ApiRequestTest
 * @covers Addframe\Mediawiki\Api\Request
 */

class ApiRequestTest extends MediawikiTestCase{

	/**
	 * @dataProvider provideConstructionData
	 */
	function testCanConstruct( $params = array(), $shouldBePosted = false, $cache = false ){
		$request = new Request( $params, $shouldBePosted , $cache );
		$this->assertInstanceOf( 'Addframe\Mediawiki\Api\Request', $request );

		//Sort out our expected params
		if( !array_key_exists( 'format', $params ) ){
			$params['format'] = 'json';
		}
		foreach( $params as $param => $value ) {
			if ( is_array( $value ) ) {
				$params[ $param ] = implode( '|', $value );
			}
		}

		//check the params
		$this->assertEquals( $params, $request->getParameters() );
		//check the defaults
		$this->assertEquals( $cache, $request->maxCacheAge() );
		$this->assertEquals( $shouldBePosted,  $request->shouldBePosted() );
		$this->assertEquals( null, $request->getResult() );
	}

	function provideConstructionData(){
		return array(
			//data, //post, //cacheable
			array( array() ),
			array( array( 'param' => 'provideConstructionData' ) ),
			array( array( 'param' => 'provideConstructionData' ), true ),
			array( array( 'param' => 'provideConstructionData' ), true ),
			array( array( 'param' => 'provideConstructionData', 'param2' => 'value2' ), false ),
			array( array( 'param' => array( 'val1', 'val2' ) ) ),
			array( array( 'param' => array( 'val1', 'val2', 'val3', 'val4' ) ) ),
			array( array( 'param' => array( 'val1', 'val2' ), 'another' => array( 'aa1', 'aa2' ) ) ),
			array( array( 'param' => 'val', 'another' => null ) ),
			array( array( 'param' => null, 'another' => 'val' ) ),
		);
	}

	function testSetParameter(){
		$expected = array();
		$request = new Request( $expected, false , 0 );
		$expected = array_merge( $expected, array( 'format' => 'json' ) );
		$this->assertEquals( $expected, $request->getParameters() );

		$request->setParameter( 'token', '863bb60669575ac8619662ddad5fc2ac+\\' );
		$expected = array_merge( $expected, array( 'token' => '863bb60669575ac8619662ddad5fc2ac+\\' ) );
		$this->assertEquals( $expected, $request->getParameters() );
	}

	function testSetResult(){
		$expected = array( 'testSetResult' );
		$request = new Request();
		$request->setResult( $expected );
		$this->assertEquals( $expected, $request->getResult() );
	}

	function testGetCacheData(){
		$expected = array( 'testGetCacheData' );
		$request = new Request();
		$request->setResult( $expected );
		$this->assertEquals( $expected, $request->getCacheData() );
	}

	function testHash(){
		$request1 = new Request( array() );
		$request2 = new Request( array() );
		$this->assertEquals( $request1->getHash(), $request2->getHash() );
		$request1 = new Request( array( 'key' => 'value' ) );
		$request2 = new Request( array() );
		$this->assertNotEquals( $request1->getHash(), $request2->getHash() );
		$request1 = new Request( array( 'key' => 'SomeLongValues?afg?2rq' ) );
		$request2 = new Request( array() );
		$this->assertNotEquals( $request1->getHash(), $request2->getHash() );
	}

	function testApiRequestOnlyStripsBadParamsWhenRequested(){
		$request = new Request();
		$request->setParameter( 'blub', 'bloo' );
		$params = $request->getParameters();
		$this->assertArrayHasKey( 'blub', $params );
		$this->assertEquals( 'bloo', $params['blub'] );

		$request = new Request( array(), false, 0, array( 'blub' ) );
		$request->setParameter( 'blub', 'bloo' );
		$request->setParameter( 'fail', 'foo' );
		$params = $request->getParameters();
		$this->assertArrayHasKey( 'blub', $params );
		$this->assertEquals( 'bloo', $params['blub'] );
		$this->assertArrayNotHasKey( 'fail', $params );
	}

}