<?php

namespace Addframe\Test;

/**
 * Class RevisionsRequestTest
 * @covers Addframe\Mediawiki\Api\RevisionsRequest
 */
class RevisionsRequestTest extends MediawikiTestCase {

	function testCanConstruct( ){
		$class = '\Addframe\Mediawiki\Api\RevisionsRequest';
		$object = new $class();
		$this->assertInstanceOf( $class, $object );

	}

	function testRevisionsRequest(){
		$query = new \Addframe\Mediawiki\Api\RevisionsRequest();
		$params = $query->getParameters();
		$this->assertArrayHasKey( 'prop', $params );
		$this->assertEquals( 'revisions', $params['prop'] );
	}

}