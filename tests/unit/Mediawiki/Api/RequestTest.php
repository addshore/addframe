<?php

namespace Addframe\Test\Unit;

use Addframe\Mediawiki\Api\Request;

/**
 * Class ApiRequestTest
 * @covers Addframe\Mediawiki\Api\Request
 */
class ApiRequestTest extends MediawikiTestCase{

	/**
	 * @dataProvider provideConstructionData
	 */
	public function testCanConstruct( $params = array(), $shouldBePosted = false, $allowedParams = array() ){
		$request = new Request( $params, $shouldBePosted, $allowedParams );
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
		$this->assertEquals( $shouldBePosted,  $request->shouldPost() );
		$this->assertEquals( null, $request->getResult() );
	}

	public function provideConstructionData(){
		return array(
			//data, //post
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
			array( array(), true, array() ),
		);
	}

	/**
	 * @dataProvider provideBadConstructionData
	 */
	public function testConstructionWithBadData( $params , $shouldBePosted , $allowedParams ){
		$this->setExpectedException( 'UnexpectedValueException' );
		new Request( $params, $shouldBePosted, $allowedParams );
	}

	public function provideBadConstructionData(){
		return array(
			//data, //post, //allowedParams
			array( null, null, null ),
			array( array(), array(), array() ),
			array( 'str', 'str', 'str' ),
			array( false, false, false ),
			array( 5, 5, 5 ),
			array( 'fail' , true, array() ),
			array( array(), 'fail', array() ),
			array( array(), true, 'fail' ),
		);
	}

	public function testSetParameter(){
		$expected = array();
		$request = new Request( $expected, false );
		$expected = array_merge( $expected, array( 'format' => 'json' ) );
		$this->assertEquals( $expected, $request->getParameters() );

		$request->setParameter( 'token', '863bb60669575ac8619662ddad5fc2ac+\\' );
		$expected = array_merge( $expected, array( 'token' => '863bb60669575ac8619662ddad5fc2ac+\\' ) );
		$this->assertEquals( $expected, $request->getParameters() );
	}

	public function testSetResult(){
		$expected = array( 'testSetResult' );
		$request = new Request();
		$request->setResult( $expected );
		$this->assertEquals( $expected, $request->getResult() );
	}

	public function testApiRequestOnlyStripsBadParamsWhenRequested(){
		$request = new Request();
		$request->setParameter( 'blub', 'bloo' );
		$params = $request->getParameters();
		$this->assertArrayHasKey( 'blub', $params );
		$this->assertEquals( 'bloo', $params['blub'] );

		$request = new Request( array (), false, array ( 'blub' ) );
		$request->setParameter( 'blub', 'bloo' );
		$request->setParameter( 'fail', 'foo' );
		$params = $request->getParameters();
		$this->assertArrayHasKey( 'blub', $params );
		$this->assertEquals( 'bloo', $params['blub'] );
		$this->assertArrayNotHasKey( 'fail', $params );
	}

}