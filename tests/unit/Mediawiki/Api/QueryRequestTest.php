<?php

namespace Addframe\Test\Unit;

use Addframe\Mediawiki\Api\QueryRequest;

/**
 * Class QueryRequestTest
 * @covers Addframe\Mediawiki\Api\QueryRequest
 */
class QueryRequestTest extends MediawikiTestCase {

	function testCanConstruct( ){
		$class = '\Addframe\Mediawiki\Api\QueryRequest';
		$object = new $class();
		$this->assertInstanceOf( $class, $object );

	}

	function testQueryRequest(){
		$query = new QueryRequest();
		$params = $query->getParameters();
		$this->assertArrayHasKey( 'action', $params );
		$this->assertEquals( 'query', $params['action'] );
	}

}