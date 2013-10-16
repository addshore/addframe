<?php

namespace Addframe\Test;

/**
 * Class TokensRequestTest
 * @covers Addframe\Mediawiki\Api\TokensRequest
 */
class TokensRequestTest extends MediawikiTestCase {

	function testCanConstruct( ){
		$class = '\Addframe\Mediawiki\Api\TokensRequest';
		$object = new $class();
		$this->assertInstanceOf( $class, $object );

	}

	function testTokensRequest(){
		$query = new \Addframe\Mediawiki\Api\TokensRequest();
		$params = $query->getParameters();
		$this->assertArrayHasKey( 'action', $params );
		$this->assertEquals( 'tokens', $params['action'] );
		$this->assertArrayHasKey( 'type', $params );
		$this->assertEquals( 'edit', $params['type'] );

		$query = new \Addframe\Mediawiki\Api\TokensRequest( array( 'type' => 'protect' ) );
		$params = $query->getParameters();
		$this->assertArrayHasKey( 'action', $params );
		$this->assertEquals( 'tokens', $params['action'] );
		$this->assertArrayHasKey( 'type', $params );
		$this->assertEquals( 'protect', $params['type'] );
	}

}