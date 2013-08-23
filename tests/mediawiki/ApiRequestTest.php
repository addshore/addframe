<?php

use Addframe\Mediawiki\Api;
use Addframe\Mediawiki\ApiRequest;
use Addframe\TestHttp;

class ApiRequestTest extends PHPUnit_Framework_TestCase{

	/**
	 * @dataProvider provideConstructionData
	 */
	function testCanConstruct( $data = array(), $format = 'php'  ){
		$request = new ApiRequest( $data, $format );
		$this->assertInstanceOf( 'Addframe\Mediawiki\ApiRequest', $request );

		$this->assertEquals( $request->getParameters(), $data );
		$this->assertEquals( $request->getFormat(), $format );
		$this->assertEquals( $request->isCacheable(), false );
		$this->assertEquals( $request->isPost(), false );
	}

	function provideConstructionData(){
		return array(
			//data, //post, //format, //cacheable
			array( ),
			array( array() ),
			array( array( 'param' => 'value' ) ),
			array( array( 'param' => 'value' ), 'php', true ),
			array( array( 'param' => 'value' ), 'json', true ),
			array( array( 'param' => 'value', 'param2' => 'value2' ), false, 'json' ),
		);
	}

}