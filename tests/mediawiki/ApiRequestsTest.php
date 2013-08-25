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
class ApiRequestsTest extends PHPUnit_Framework_TestCase{

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
		$paramString = 'paramValue';
		return array(
			array( 'QueryRequest', $paramArray ),
			array( 'SiteInfoRequest', $paramString),
			array( 'LoginRequest', $paramString),
			array( 'LogoutRequest', null),
			array( 'TokensRequest', $paramString),
			array( 'EditRequest', $paramArray),
		);
	}

}