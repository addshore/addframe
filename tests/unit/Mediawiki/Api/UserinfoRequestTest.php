<?php

namespace Addframe\Test\Unit;

use Addframe\Mediawiki\Api\UserinfoRequest;

/**
 * Class UserinfoRequestTest
 * @covers Addframe\Mediawiki\Api\UserinfoRequest
 */
class UserinfoRequestTest extends MediawikiTestCase {

	function testCanConstruct( ){
		$class = '\Addframe\Mediawiki\Api\UserinfoRequest';
		$object = new $class();
		$this->assertInstanceOf( $class, $object );

	}

	function testSitematrixRequest(){
		$query = new UserinfoRequest();
		$params = $query->getParameters();
		$this->assertArrayHasKey( 'meta', $params );
		$this->assertEquals( 'userinfo', $params['meta'] );
	}

}