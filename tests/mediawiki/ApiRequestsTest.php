<?php

/**
 * Class For testing extra API requests
 * @covers Addframe\Mediawiki\QueryRequest
 * @covers Addframe\Mediawiki\SiteInfoRequest
 * @covers Addframe\Mediawiki\LoginRequest
 * @covers Addframe\Mediawiki\LogoutRequest
 * @covers Addframe\Mediawiki\TokensRequest
 * @covers Addframe\Mediawiki\EditRequest
 */
class ApiRequestsTest extends MediawikiTestCase{

	/**
	 * @dataProvider provideConstructionData
	 */
	function testCanConstruct( $class, $data ){
		$class = 'Addframe\Mediawiki\\'.$class;

		if( is_null( $data ) ){
			$object = new $class();
		} else {
			$object = new $class( $data );
		}

		$this->assertInstanceOf( $class, $object );

	}

	function provideConstructionData(){
		$paramArray = array( 'param' => 'value');
		return array(
			array( 'QueryRequest', $paramArray ),
			array( 'SiteInfoRequest', $paramArray),
			array( 'LoginRequest', $paramArray),
			array( 'LogoutRequest', null),
			array( 'TokensRequest', $paramArray),
			array( 'EditRequest', $paramArray),
		);
	}

}