<?php

use Addframe\Mediawiki\Site;

class SiteTest extends PHPUnit_Framework_TestCase {

	function testCanConstruct(){
		$site = new Site();
		$this->assertInstanceOf( 'Addframe\Mediawiki\Site', $site );
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
	function testGetApiUrl( $apiUrl, $mockHtml ){
		$site = new Site( $this->getMockHttp( $mockHtml ) );
		$site->setUrl( 'localhost' );
		$this->assertEquals( $apiUrl, $site->getApiUrl() );
	}

	function provideGetApiUrl(){
		$before = '<link rel="search" type="application/opensearchdescription+xml" href="/mediawiki/opensearch_desc.php"'.
			' title="Local Test Wiki (en-gb)" />'."\n".'<link rel="EditURI" type="application/rsd+xml" href="http://';
		$after = '?action=rsd" />'."\n".'<link rel="alternate" type="application/atom+xml" title="Local Test Wiki Atom feed'.
			'" href="/mediawiki/index.php?title=Special:RecentChanges&amp;feed=atom" />';

		$apiLocations = Array(
			'localhost/mediawiki/api.php',
			'en.wikipedia.org/w/api.php',
			'zh-classic.wikivoyage.org/w/api.php',
			'127.0.0.1/api.php',
		);

		$toReturn = array();
		foreach( $apiLocations as $apiUrl ){
			$toReturn[] = array( $apiUrl, array( 0 => $before.$apiUrl.$after ) );
		}

		return $toReturn;

	}

	function getMockHttp( $requestResult = array( 0 => '' ) ){
		$http = $this->getMock( 'Addframe\Http', array('get','post') );
		foreach( $requestResult as $key => $return ){
			$http->expects( $this->at( $key ) )->method( 'get' )->will( $this->returnValue( $return ) );
			$http->expects( $this->at( $key ) )->method( 'post' )->will( $this->returnValue( $return ) );
		}
		return $http;
	}

}