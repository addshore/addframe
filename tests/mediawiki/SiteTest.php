<?php

use Addframe\Mediawiki\Api;
use Addframe\Mediawiki\Site;
use Addframe\Mediawiki\TestApi;
use Addframe\TestHttp;

class SiteTest extends PHPUnit_Framework_TestCase {

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
			array( 'edit', '{"tokens":{"edittoken":"+\\\\"}}', '+\\'),
			array( 'protect', '{"tokens":{"protecttoken":"+\\\\"}}', '+\\'),
			array( 'watch', '{"tokens":{"watchtoken":"863bb60669575ac8619662ddad5fc2ac+\\\\"}}', '863bb60669575ac8619662ddad5fc2ac+\\' ),
			array( 'patrol', '{"tokens":{"patroltoken":"9104118c9a64b875153bbace79da58e8+\\\\"}}', '9104118c9a64b875153bbace79da58e8+\\' ),
			array( 'foo', '{"warnings":{"tokens":{"*":"Action \'foo\' is not allowed for the current user"}}}', null ),
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
			array( '{"tokens":{"edittoken":"+\\\\"}}', array( 'edittoken' => '+\\' ) ),
			array( '{"tokens":{"protecttoken":"863bb60669575ac8619662ddad5fc2ac+\\\\"}}', array( 'protecttoken' => '863bb60669575ac8619662ddad5fc2ac+\\' ) ),
			array( '{"tokens":{"protecttoken":"+\\\\","patroltoken":"+\\\\"}}', array( 'protecttoken' => '+\\', 'patroltoken' => '+\\' ) ),
			array( '{"warnings":{"tokens":{"*":"Action \'foo\' is not allowed for the current user"}}}', array() ),
			array( '{"warnings":{"tokens":{"*":"Action \'foo\' is not allowed for the current user"}},"tokens":{"edittoken":"+\\\\"}}', array( 'edittoken' => '+\\' ) ),
		);
	}

}