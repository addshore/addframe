<?php

use Addframe\Mediawiki\Site;
use Addframe\Mediawiki\TestApi;
use Addframe\TestHttp;

class SiteTest extends InjectDataTestCase {

	function testCanConstruct(){
		$site = new Site();
		$this->assertInstanceOf( 'Addframe\Mediawiki\Site', $site );
	}

	/**
	 * @dataProvider provideUrls
	 */
	function testCanGetNewFromUrl( $url ){
		$site = Site::newFromUrl( $url );
		$site->setUrl( $url );
		$this->assertEquals( $url, $site->getUrl() );
	}

	/**
	 * @dataProvider provideUrls
	 */
	function testCanSetUrl( $url ){
		$site = new Site();
		$site->setUrl( $url );
		$this->assertEquals( $url, $site->getUrl() );
	}

	function provideUrls(){
		return array(
			array( 'localhost/mediawiki' ),
			array( '//127.0.0.1/' ),
			array( 'en.wikipedia.org/wiki' ),
			array( 'http://de.wikipedia.org/wiki/' ),
			array( 'https://es.wikipedia.org/wiki' ),
			array( '//pt.imawiki.org' ),
		);
	}

	/**
	 * @dataProvider provideGetApiUrl
	 */
	function testGetApi( $apiUrl, $mockHtml ){
		$http = new TestHttp( $mockHtml );

		$site = new Site( $http );
		$site->setUrl( 'localhost' );

		$this->assertInstanceOf( 'Addframe\Mediawiki\Api', $site->getApi() );
		$this->assertEquals( $apiUrl, $site->getApi()->getUrl() );
	}

	function provideGetApiUrl(){
		$before = '<link rel="search" type="application/opensearchdescription+xml" href="/mediawiki/opensearch_desc.php"'.
			' title="Local Test Wiki (en-gb)" />'."\n".'<link rel="EditURI" type="application/rsd+xml" href="';
		$after = '?action=rsd" />'."\n".'<link rel="alternate" type="application/atom+xml" title="Local Test Wiki Atom feed'.
			'" href="/mediawiki/index.php?title=Special:RecentChanges&amp;feed=atom" />';

		$apiLocations = Array(
			'http://localhost/mediawiki/api.php',
			'https://en.wikipedia.org/w/api.php',
			'//zh-classic.wikivoyage.org/w/api.php',
			'http://127.0.0.1/api.php',
		);

		$toReturn = array();
		foreach( $apiLocations as $apiUrl ){
			$toReturn[] = array( $apiUrl, $before.$apiUrl.$after );
		}

		return $toReturn;

	}

	/**
	 * @dataProvider provideGetToken
	 */
	function testGetToken( $type = 'edit', $json, $expected){
		$site = Site::newFromUrl( 'foobar' );
		$site->setApi( new TestApi( $json ) );
		$token = $site->getToken( $type );
		$this->assertEquals( $expected, $token );
	}

	function provideGetToken(){
		return array(
			array( 'edit', $this->getData( 'tokens/anonedittoken.json' ), '+\\'),
			array( 'protect', $this->getData( 'tokens/protecttoken.json' ), '863bb60669575ac8619662ddad5fc2ac+\\'),
			array( 'watch', $this->getData( 'tokens/watchtoken.json' ), 'A63bb60669575ac8619662ddad5fc2ac+\\' ),
			array( 'foo', $this->getData( 'tokens/warnings.json' ), null ),
		);
	}

	/**
	 * @dataProvider provideGetTokenList
	 */
	function testGetTokenList( $json, $expected){
		$site = Site::newFromUrl( 'foobar' );
		$site->setApi( new TestApi( $json ) );
		$this->assertEquals( $expected, $site->getTokenList() );
	}

	function provideGetTokenList(){
		return array(
			array( $this->getData( 'tokens/anonedittoken.json' ), array( 'edittoken' => '+\\' ) ),
			array( $this->getData( 'tokens/protecttoken.json' ), array( 'protecttoken' => '863bb60669575ac8619662ddad5fc2ac+\\' ) ),
			array( $this->getData( 'tokens/warnings.json' ), array() ),
			array( $this->getData( 'tokens/anoneditandwarnings.json' ) , array( 'edittoken' => '+\\' ) ),
		);
	}

	/**
	 * @dataProvider provideLogin
	 */
	function testLogin( $injectedResult, $expected){
		if( array_key_exists( 'exception', $expected ) ){
			$this->setExpectedException( $expected['exception'] );
		}
		$site = Site::newFromUrl( 'foobar' );
		$site->setApi( new TestApi( $injectedResult ) );
		$result = $site->login( 'foo', 'bar' );
		$this->assertEquals( $expected['value'], $result );
	}

	function provideLogin(){
		return array(
			array(
				array( file_get_contents( __DIR__.'/data/login/part1.json' ), file_get_contents( __DIR__.'/data/login/part2.json' ) ),
				array( 'value' => true ) ),
			array(
				array( file_get_contents( __DIR__.'/data/login/wrongtoken.json' ) ),
				array( 'exception' => 'Exception' ) ),
		);
	}

}