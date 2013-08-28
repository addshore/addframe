<?php

/**
 * Class For testing extra API requests
 * @covers Addframe\Mediawiki\QueryRequest
 * @covers Addframe\Mediawiki\SiteInfoRequest
 * @covers Addframe\Mediawiki\LoginRequest
 * @covers Addframe\Mediawiki\LogoutRequest
 * @covers Addframe\Mediawiki\TokensRequest
 * @covers Addframe\Mediawiki\EditRequest
 */
class ApiRequestsTest extends MediawikiTestCase{

	/**
	 * @dataProvider provideConstructionData
	 */
	function testCanConstruct( $class ){
		$class = 'Addframe\Mediawiki\\'.$class;
		$object = new $class();
		$this->assertInstanceOf( $class, $object );

	}

	function provideConstructionData(){
		return array(
			array( 'QueryRequest' ),
			array( 'SiteInfoRequest' ),
			array( 'LoginRequest' ),
			array( 'LogoutRequest' ),
			array( 'TokensRequest' ),
			array( 'EditRequest' ),
		);
	}

	function testQueryRequest(){
		$query = new \Addframe\Mediawiki\QueryRequest();
		$params = $query->getParameters();
		$this->assertArrayHasKey( 'action', $params );
		$this->assertEquals( 'query', $params['action'] );
	}

	function testSiteInfoRequest(){
		$query = new \Addframe\Mediawiki\SiteInfoRequest();
		$params = $query->getParameters();
		$this->assertArrayHasKey( 'meta', $params );
		$this->assertEquals( 'siteinfo', $params['meta'] );
	}

	function testLoginRequest(){
		$query = new \Addframe\Mediawiki\LoginRequest();
		$params = $query->getParameters();
		$this->assertArrayHasKey( 'action', $params );
		$this->assertEquals( 'login', $params['action'] );
	}

	function testLogoutRequest(){
		$query = new \Addframe\Mediawiki\LogoutRequest();
		$params = $query->getParameters();
		$this->assertArrayHasKey( 'action', $params );
		$this->assertEquals( 'logout', $params['action'] );
	}

	function testTokensRequest(){
		$query = new \Addframe\Mediawiki\TokensRequest();
		$params = $query->getParameters();
		$this->assertArrayHasKey( 'action', $params );
		$this->assertEquals( 'tokens', $params['action'] );
		$this->assertArrayHasKey( 'type', $params );
		$this->assertEquals( 'edit', $params['type'] );

		$query = new \Addframe\Mediawiki\TokensRequest( array( 'type' => 'protect' ) );
		$params = $query->getParameters();
		$this->assertArrayHasKey( 'action', $params );
		$this->assertEquals( 'tokens', $params['action'] );
		$this->assertArrayHasKey( 'type', $params );
		$this->assertEquals( 'protect', $params['type'] );
	}

	function testEditRequest(){
		$query = new \Addframe\Mediawiki\EditRequest();
		$params = $query->getParameters();
		$this->assertArrayHasKey( 'action', $params );
		$this->assertEquals( 'edit', $params['action'] );

		$query = new \Addframe\Mediawiki\EditRequest( array( 'text' => 'FooBar' ) );
		$params = $query->getParameters();
		$this->assertArrayHasKey( 'action', $params );
		$this->assertEquals( 'edit', $params['action'] );
		$this->assertArrayHasKey( 'text', $params );
		$this->assertEquals( 'FooBar', $params['text'] );
		$this->assertArrayHasKey( 'md5', $params );
		$this->assertEquals( md5( 'FooBar' ), $params['md5'] );

		$query = new \Addframe\Mediawiki\EditRequest( array( 'prependtext' => 'AtTheStart', 'appendtext' => 'AtTheEnd' ) );
		$params = $query->getParameters();
		$this->assertArrayHasKey( 'action', $params );
		$this->assertEquals( 'edit', $params['action'] );
		$this->assertArrayHasKey( 'prependtext', $params );
		$this->assertEquals( 'AtTheStart', $params['prependtext'] );
		$this->assertArrayHasKey( 'appendtext', $params );
		$this->assertEquals( 'AtTheEnd', $params['appendtext'] );
		$this->assertArrayHasKey( 'md5', $params );
		$this->assertEquals( md5( 'AtTheStart' . 'AtTheEnd' ), $params['md5'] );
	}

}