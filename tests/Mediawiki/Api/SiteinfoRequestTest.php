<?php

namespace Addframe\Test;

/**
 * Class SiteinfoRequestTest
 * @covers Addframe\Mediawiki\Api\SiteinfoRequest
 */
class SiteinfoRequestTest extends MediawikiTestCase {

	function testCanConstruct( ){
		$class = '\Addframe\Mediawiki\Api\SiteinfoRequest';
		$object = new $class();
		$this->assertInstanceOf( $class, $object );

	}

	function testSiteInfoRequest(){
		$query = new \Addframe\Mediawiki\Api\SiteinfoRequest();
		$params = $query->getParameters();
		$this->assertArrayHasKey( 'meta', $params );
		$this->assertEquals( 'siteinfo', $params['meta'] );
	}

}