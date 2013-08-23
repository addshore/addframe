<?php

use Addframe\Mediawiki\ApiRequest;
use Addframe\Mediawiki\SiteInfoRequest;

class ApiRequestTest extends PHPUnit_Framework_TestCase{

	/**
	 * @dataProvider provideConstructionData
	 */
	function testCanConstruct( $params = array(), $shouldBePosted = false, $cache = false ){
		$request = new ApiRequest( $params, $shouldBePosted , $cache );
		$this->assertInstanceOf( 'Addframe\Mediawiki\ApiRequest', $request );

		if( !array_key_exists( 'format', $params ) ){
			$params['format'] = 'php';
		}

		//check the params
		$this->assertEquals( $request->getParameters(), $params );
		//check the defaults
		$this->assertEquals( $request->isCacheable(), $cache );
		$this->assertEquals( $request->isPost(), $shouldBePosted );
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

}