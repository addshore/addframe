<?php

namespace Addframe\Test\Unit;

use Addframe\Mediawiki\Api\SiteinfoRequest;

/**
 * Class SiteinfoRequestTest
 * @covers Addframe\Mediawiki\Api\SiteinfoRequest
 */
class SiteinfoRequestTest extends MediawikiTestCase {

	public function testCanConstruct( ){
		$class = '\Addframe\Mediawiki\Api\SiteinfoRequest';
		$object = new $class();
		$this->assertInstanceOf( $class, $object );

	}

	public function testSiteInfoRequest(){
		$query = new SiteinfoRequest();
		$params = $query->getParameters();
		$this->assertArrayHasKey( 'meta', $params );
		$this->assertEquals( 'siteinfo', $params['meta'] );
	}

}