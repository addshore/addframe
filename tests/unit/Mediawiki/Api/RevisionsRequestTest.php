<?php

namespace Addframe\Test;

use Addframe\Mediawiki\Api\RevisionsRequest;

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
		$query = new RevisionsRequest();
		$params = $query->getParameters();
		$this->assertArrayHasKey( 'prop', $params );
		$this->assertEquals( 'revisions', $params['prop'] );
	}

}