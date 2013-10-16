<?php

namespace Addframe\Test\Unit;

use Addframe\Mediawiki\Api\LoginRequest;

/**
 * Class LoginRequestTest
 * @covers Addframe\Mediawiki\Api\LoginRequest
 */
class LoginRequestTest extends MediawikiTestCase {

	public function testCanConstruct( ){
		$class = '\Addframe\Mediawiki\Api\LoginRequest';
		$object = new $class();
		$this->assertInstanceOf( $class, $object );

	}

	public function testLoginRequest(){
		$query = new LoginRequest();
		$params = $query->getParameters();
		$this->assertArrayHasKey( 'action', $params );
		$this->assertEquals( 'login', $params['action'] );
	}

}