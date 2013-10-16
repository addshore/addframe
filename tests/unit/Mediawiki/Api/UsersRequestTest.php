<?php

namespace Addframe\Test\Unit;

use Addframe\Mediawiki\Api\UsersRequest;

/**
 * Class UsersRequestTest
 * @covers Addframe\Mediawiki\Api\UsersRequest
 */
class UsersRequestTest extends MediawikiTestCase {

	public function testCanConstruct( ){
		$class = '\Addframe\Mediawiki\Api\UsersRequest';
		$object = new $class();
		$this->assertInstanceOf( $class, $object );

	}

	public function testUsersRequest(){
		$query = new UsersRequest();
		$params = $query->getParameters();
		$this->assertArrayHasKey( 'list', $params );
		$this->assertEquals( 'users', $params['list'] );
		$this->assertArrayHasKey( 'usprop', $params );
		$this->assertEquals( 'blockinfo|groups|implicitgroups|rights|editcount|registration|emailable|gender', $params['usprop'] );
	}

}