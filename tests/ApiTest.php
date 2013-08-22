<?php

use Addframe\Mediawiki\Api;

class ApiTest extends PHPUnit_Framework_TestCase{

	function testCanConstruct( ){
		$site = new Api( );
		$this->assertInstanceOf( 'Addframe\Mediawiki\Api', $site );
	}

	/**
	 * @dataProvider provideUrls
	 */
	function testCanGetNewFromUrl( $url ){
		$site = Api::newFromUrl( $url );
		$site->setUrl( $url );
		$this->assertEquals( $url, $site->getUrl() );
	}

	/**
	 * @dataProvider provideUrls
	 */
	function testCanSetUrl( $url ){
		$site = new Api();
		$site->setUrl( $url );
		$this->assertEquals( $url, $site->getUrl() );
	}

	function provideUrls(){
		return array(
			array( 'localhost/mediawiki/api.php' ),
			array( '127.0.0.1/api.php' ),
			array( 'en.wikipedia.org/wiki/api.php' ),
		);
	}

}