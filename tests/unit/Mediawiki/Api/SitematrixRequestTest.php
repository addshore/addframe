<?php

namespace Addframe\Test\Unit;

use Addframe\Mediawiki\Api\SitematrixRequest;

/**
 * Class SitematrixRequestTest
 * @covers Addframe\Mediawiki\Api\SitematrixRequest
 */
class SitematrixRequestTest extends MediawikiTestCase {

	public function testCanConstruct( ){
		$class = '\Addframe\Mediawiki\Api\SitematrixRequest';
		$object = new $class();
		$this->assertInstanceOf( $class, $object );

	}

	public function testSitematrixRequest(){
		$query = new SitematrixRequest();
		$params = $query->getParameters();
		$this->assertArrayHasKey( 'action', $params );
		$this->assertEquals( 'sitematrix', $params['action'] );
	}

}