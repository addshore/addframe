<?php

namespace Addframe\Test\Unit;

use Addframe\Mediawiki\Api\LogoutRequest;

/**
 * Class LogoutRequestTest
 * @covers Addframe\Mediawiki\Api\LogoutRequest
 */
class LogoutRequestTest extends MediawikiTestCase {

	public function testCanConstruct( ){
		$class = '\Addframe\Mediawiki\Api\LogoutRequest';
		$object = new $class();
		$this->assertInstanceOf( $class, $object );
	}

	public function testLogoutRequest(){
		$query = new LogoutRequest();
		$params = $query->getParameters();
		$this->assertArrayHasKey( 'action', $params );
		$this->assertEquals( 'logout', $params['action'] );
	}

}