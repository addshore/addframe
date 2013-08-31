<?php

/**
 * Class SitematrixRequestTest
 * @covers Addframe\Mediawiki\Api\SitematrixRequest
 */
class SitematrixRequestTest extends MediawikiTestCase {

	function testCanConstruct( ){
		$class = '\Addframe\Mediawiki\Api\SitematrixRequest';
		$object = new $class();
		$this->assertInstanceOf( $class, $object );

	}

	function testSitematrixRequest(){
		$query = new \Addframe\Mediawiki\Api\SitematrixRequest();
		$params = $query->getParameters();
		$this->assertArrayHasKey( 'action', $params );
		$this->assertEquals( 'sitematrix', $params['action'] );
	}

}