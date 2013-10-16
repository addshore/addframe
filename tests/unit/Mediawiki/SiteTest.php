<?php

namespace Addframe\Test\Unit;

use Addframe\Mediawiki\Site;
use Addframe\Mediawiki\TestApi;
use Addframe\TestHttp;

/**
 * Class SiteTest
 * @covers Addframe\Mediawiki\Site
 */

class SiteTest extends MediawikiTestCase{

	public function testCanConstruct(){
		$site = new Site();
		$this->assertInstanceOf( 'Addframe\Mediawiki\Site', $site );
	}

	/**
	 * @dataProvider provideUrls
	 */
	public function testCanGetNewFromUrl( $url, $expect ){
		$site = Site::newFromUrl( $url );
		$site->setUrl( $url );
		$this->assertEquals( $expect, $site->getUrl() );
	}

	/**
	 * @dataProvider provideUrls
	 */
	public function testCanSetUrl( $url, $expect ){
		$site = new Site();
		$site->setUrl( $url );
		$this->assertEquals( $expect, $site->getUrl() );
	}

	public function provideUrls(){
		return array(
			array( 'localhost/mediawiki', 'localhost/mediawiki' ),
			array( '//127.0.0.1/', '127.0.0.1' ),
			array( 'en.wikipedia.org/wiki' , 'en.wikipedia.org/wiki' ),
			array( 'http://de.wikipedia.org/wiki/', 'de.wikipedia.org/wiki' ),
			array( 'https://es.wikipedia.org/wiki' , 'es.wikipedia.org/wiki' ),
			array( '//pt.imawiki.org' , 'pt.imawiki.org'),
		);
	}

	/**
	 * @dataProvider provideGetApiUrl
	 */
	public function testGetApi( $apiUrl, $mockHtml ){
		$http = new TestHttp( $mockHtml );

		$site = new Site( $http );
		$site->setUrl( 'localhost' );

		$this->assertInstanceOf( 'Addframe\Mediawiki\Api', $site->getApi() );
		$this->assertEquals( $apiUrl, $site->getApi()->getUrl() );
	}

	public function provideGetApiUrl(){
		//a section of html from below and after the api url..
		$before = '<link rel="search" type="application/opensearchdescription+xml" href="/mediawiki/opensearch_desc.php"'.
			' title="Local Test Wiki (en-gb)" />'."\n".'<link rel="EditURI" type="application/rsd+xml" href="';
		$after = '?action=rsd" />'."\n".'<link rel="alternate" type="application/atom+xml" title="Local Test Wiki Atom feed'.
			'" href="/mediawiki/index.php?title=Special:RecentChanges&amp;feed=atom" />';

		//a few different possible api locations
		$apiLocations = Array(
			'http://localhost/mediawiki/api.php',
			'https://en.wikipedia.org/w/api.php',
			'//zh-classic.wikivoyage.org/w/api.php',
			'http://127.0.0.1/api.php',
		);

		//construct and return an array of possible chunks of html combining the above
		$toReturn = array();
		foreach( $apiLocations as $apiUrl ){
			$toReturn[] = array( $apiUrl, $before.$apiUrl.$after );
		}
		return $toReturn;
	}

	public function testGetApiFromHomeFailsWithNoUrl(){
		//todo better exception..
		$this->setExpectedException( 'Exception' );
		$site = new Site();
		$site->getApiFromHomePage();
	}

	/**
	 * @dataProvider provideGetToken
	 */
	public function testGetToken( $type = 'edit', $json, $expected){
		$site = Site::newFromUrl( 'foobar' );
		$api = new TestApi( $json );
		$site->setApi( $api );
		$token = $site->getToken( $type );

		$this->assertEquals( $expected, $token );
		$this->assertEquals( 1, count( $api->completeRequests ) );
		$params = $api->completeRequests[0]->getParameters();
		$this->assertArrayHasKey( 'type', $params );
		$this->assertEquals( $type, $params['type'] );
	}

	public function provideGetToken(){
		return array(
			array( 'edit', $this->getTestApiData( 'tokens/anonedittoken.json' ), '+\\'),
			array( 'protect', $this->getTestApiData( 'tokens/protecttoken.json' ), '863bb60669575ac8619662ddad5fc2ac+\\'),
			array( 'watch', $this->getTestApiData( 'tokens/watchtoken.json' ), 'A63bb60669575ac8619662ddad5fc2ac+\\' ),
			array( 'foo', $this->getTestApiData( 'tokens/warnings.json' ), null ),
		);
	}

	/**
	 * @dataProvider provideGetTokenList
	 */
	public function testGetTokenList( $json, $expected){
		$site = Site::newFromUrl( 'foobar' );
		$api = new TestApi( $json );
		$site->setApi( $api );
		$tokenlist = $site->getTokenList();

		$this->assertEquals( $expected, $tokenlist );
		$this->assertEquals( 1, count( $api->completeRequests ) );
		$params = $api->completeRequests[0]->getParameters();
		$this->assertArrayHasKey( 'type', $params );
		$this->assertEquals( 'block|delete|edit|email|import|move|options|patrol|protect|unblock|watch', $params['type'] );
	}

	public function provideGetTokenList(){
		return array(
			array( $this->getTestApiData( 'tokens/anonedittoken.json' ), array( 'edittoken' => '+\\' ) ),
			array( $this->getTestApiData( 'tokens/protecttoken.json' ), array( 'protecttoken' => '863bb60669575ac8619662ddad5fc2ac+\\' ) ),
			array( $this->getTestApiData( 'tokens/warnings.json' ), array() ),
			array( $this->getTestApiData( 'tokens/anoneditandwarnings.json' ) , array( 'edittoken' => '+\\' ) ),
		);
	}

	/**
	 * @dataProvider provideLogin
	 */
	public function testLogin( $injectedResult, $expected ){
		$site = Site::newFromUrl( 'foobar' );
		$api = new TestApi( $injectedResult );
		$site->setApi( $api );
		$result = $site->login( 'foo', 'bar' );

		$this->assertEquals( $expected['result'], $result );
		$this->assertEquals( $expected['requests'], count( $api->completeRequests ) );
		foreach( $api->completeRequests as $request ){
			$params = $request->getParameters();
			$this->assertArrayHasKey( 'lgname', $params );
			$this->assertEquals( 'foo', $params['lgname'] );
			$this->assertArrayHasKey( 'lgpassword', $params );
			$this->assertEquals( 'bar', $params['lgpassword'] );
		}
	}

	public function provideLogin(){
		return array(
			array(
				array( $this->getTestApiData( 'login/part1.json' ), $this->getTestApiData( 'login/part2.json' ) ),
				array( 'result' => true, 'requests' => 2 ) ),
			array(
				array( $this->getTestApiData( 'login/part1.json' ), $this->getTestApiData( 'login/wrongtoken.json' ) ),
				array( 'result' => false, 'requests' => 2 ) ),
			array(
				$this->getTestApiData( 'login/wrongtoken.json' ),
				array( 'result' => false, 'requests' => 1 ) ),
		);
	}

	public function testLogout(){
		$site = Site::newFromUrl( 'foobar' );
		$api = new TestApi( '[]' );
		$site->setApi( $api );
		$result = $site->logout();

		$this->assertTrue( $result );
		$this->assertEquals( 1, count( $api->completeRequests ) );
	}

	public function testGetUser(){
		$site = new Site();
		$user = $site->getUser( 'Foo' );
		$this->assertInstanceOf( '\Addframe\Mediawiki\User', $user );
		$this->assertEquals( 'Foo', $user->getName() );
	}

}