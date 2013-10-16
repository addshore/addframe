<?php

namespace Addframe\Test\Unit;

use Addframe\Mediawiki\Api\UserinfoRequest;

/**
 * Class UserinfoRequestTest
 * @covers Addframe\Mediawiki\Api\UserinfoRequest
 */
class UserinfoRequestTest extends MediawikiTestCase {

	public function testCanConstruct( ){
		$class = '\Addframe\Mediawiki\Api\UserinfoRequest';
		$object = new $class();
		$this->assertInstanceOf( $class, $object );

	}

	public function testSitematrixRequest(){
		$query = new UserinfoRequest();
		$params = $query->getParameters();
		$this->assertArrayHasKey( 'meta', $params );
		$this->assertEquals( 'userinfo', $params['meta'] );
	}

}