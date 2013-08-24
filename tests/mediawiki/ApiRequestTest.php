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
		$this->assertEquals( $cache, $request->isCacheable() );
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

	/**
	 * @dataProvider provideConstructionData
	 */
	function testExecute( $params = array(), $shouldBePosted = false, $cache = false  ){
		$request = new ApiRequest( $params, $shouldBePosted , $cache );
		$api = new TestApi( '[]' );

		$result = $request->execute( $api );
		$this->assertNotNull( $request->getResult() );
		$this->assertEquals( array(), $result );
		$this->assertEquals( array(), $request->getResult() );
	}

}