<?php

namespace Addframe\Mediawiki\Tests;
use Addframe\Mediawiki\Site;

/**
 *
 * @since 0.0.2
 *
 * @author Addshore
 */

class SiteTest extends MediawikiTestCase {

	/**
	 * @dataProvider provideValidConstructionValues
	 */
	function testCanConstructFamily( $url, $http, $family ){
		new Site( $url, $http, $family );
		$this->assertTrue( true, 'Unable to construct a Site object with a url' );
	}

	function provideValidConstructionValues(){
		return array(
			array( 'localhost', null , null ),
			array( 'localhost', $this->getMock('Addframe\Http') , null ),
			array( 'en.wikipedia.org', $this->getMock('Addframe\Http') , $this->getMockFamilyForConstruction() ),
		);
	}

	/**
	 * @dataProvider provideInvalidConstructionValues
	 */
	function testCanNotConstructFamilyWithEmptyUrl( $url, $http  ){
		$this->setExpectedException('Exception', 'Can not construct a site without a url');
		new Site( $url, $http );
	}

	function provideInvalidConstructionValues(){
		return array(
			array( '', null , null ),
			array( '', $this->getMockHttp() , null ),
			array( '', $this->getMockHttp() ,$this->getMockFamilyForConstruction() ),
			array( '', null ,$this->getMockFamilyForConstruction() ),
		);
	}

	/**
	 * @dataProvider provideRequestApiUrlData
	 */
	function testGetApiUrl( $apiUrl, $pretendHtml ){
		$site = new Site( 'localhost', $this->getMockHttp( $pretendHtml ) );
		$this->assertEquals( $apiUrl, $site->getApiUrl() );
	}

	function provideRequestApiUrlData(){
		$before = '<link rel="search" type="application/opensearchdescription+xml" href="/mediawiki/opensearch_desc.php"'.
			' title="Local Test Wiki (en-gb)" />'."\n".'<link rel="EditURI" type="application/rsd+xml" href="http://';
		$after = '?action=rsd" />'."\n".'<link rel="alternate" type="application/atom+xml" title="Local Test Wiki Atom feed'.
			'" href="/mediawiki/index.php?title=Special:RecentChanges&amp;feed=atom" />';

		$apiLocations = Array(
			'localhost/mediawiki/api.php',
			'en.wikipedia.org/w/api.php',
			'zh-classic.wikivoyage.org/w/api.php'
		);

		$toReturn = array();
		foreach( $apiLocations as $apiUrl ){
			$toReturn[] = array( $apiUrl, array( 0 => $before.$apiUrl.$after ) );
		}

		return $toReturn;

	}

	function testSetUserLoginGetUserLoginRoundtrip(){
		$mockLogin = $this->getMock( 'Addframe\Mediawiki\UserLogin', array(), array('username','password') );
		$mockLogin->expects( $this->any() )->method( 'getPassword' )->will( $this->returnValue( 'password' ) );
		$site = $this->getDefaultSite();
		$site->setLogin( $mockLogin );
		$this->assertEquals( $mockLogin, $site->getUserLogin(), 'Cannot assert login was set correctly' );
		$this->assertEquals( 'password', $site->getUserLogin()->getPassword(), 'Cannot assert login was set correctly');

	}

	function getDefaultSite(){
		return new Site('localhost');
	}

}