<?php

namespace Addframe\Test;

/**
 * Class UsersRequestTest
 * @covers Addframe\Mediawiki\Api\UsersRequest
 */
class UsersRequestTest extends MediawikiTestCase {

	function testCanConstruct( ){
		$class = '\Addframe\Mediawiki\Api\UsersRequest';
		$object = new $class();
		$this->assertInstanceOf( $class, $object );

	}

	function testUsersRequest(){
		$query = new \Addframe\Mediawiki\Api\UsersRequest();
		$params = $query->getParameters();
		$this->assertArrayHasKey( 'list', $params );
		$this->assertEquals( 'users', $params['list'] );
		$this->assertArrayHasKey( 'usprop', $params );
		$this->assertEquals( 'blockinfo|groups|implicitgroups|rights|editcount|registration|emailable|gender', $params['usprop'] );
	}

}