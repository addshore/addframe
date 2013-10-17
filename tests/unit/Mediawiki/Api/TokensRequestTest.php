<?php

namespace Addframe\Test\Unit;

use Addframe\Mediawiki\Api\TokensRequest;

/**
 * Class TokensRequestTest
 * @covers Addframe\Mediawiki\Api\TokensRequest
 */
class TokensRequestTest extends MediawikiTestCase {

	public function testCanConstruct( ){
		$class = '\Addframe\Mediawiki\Api\TokensRequest';
		$object = new $class();
		$this->assertInstanceOf( $class, $object );

	}

	public function testTokensRequest(){
		$query = new TokensRequest();
		$params = $query->getParameters();
		$this->assertArrayHasKey( 'action', $params );
		$this->assertEquals( 'tokens', $params['action'] );
		$this->assertArrayHasKey( 'type', $params );
		$this->assertEquals( 'edit', $params['type'] );

		$query = new TokensRequest( array ( 'type' => 'protect' ) );
		$params = $query->getParameters();
		$this->assertArrayHasKey( 'action', $params );
		$this->assertEquals( 'tokens', $params['action'] );
		$this->assertArrayHasKey( 'type', $params );
		$this->assertEquals( 'protect', $params['type'] );
	}

}