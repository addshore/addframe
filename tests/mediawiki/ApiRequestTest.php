<?php

use Addframe\Mediawiki\ApiRequest;

class ApiRequestTest extends PHPUnit_Framework_TestCase{

	/**
	 * @dataProvider provideConstructionData
	 */
	function testCanConstruct( $data = array(), $post = false, $format = 'php', $cacheable = false ){
		$request = new ApiRequest( $data, $post, $format, $cacheable );
		$this->assertInstanceOf( 'Addframe\Mediawiki\ApiRequest', $request );

		$this->assertEquals( $request->getData(), $data );
		$this->assertEquals( $request->isPost(), $post );
		$this->assertEquals( $request->getFormat(), $format );
		$this->assertEquals( $request->isCacheable(), $cacheable );
	}

	function provideConstructionData(){
		return array(
			//data, //post, //format, //cacheable
			array( ),
			array( array() ),
			array( array( 'param' => 'value' ) ),
			array( array( 'param' => 'value' ), true ),
			array( array( 'param' => 'value' ), false, 'php', true ),
			array( array( 'param' => 'value' ), false, 'json', true ),
			array( array( 'param' => 'value', 'param2' => 'value2' ), false, 'json', true ),
		);
	}

}