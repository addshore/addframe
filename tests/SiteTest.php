<?php

namespace Addframe\Tests;

use Addframe\Site;

class SiteTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @dataProvider provideValidConstructionValues
	 */
	function testCanConstructFamily( $values ){
		new Site( $values[0], $values[1], $values[2] );
		$this->assertTrue( true, 'Unable to construct a Site object with a url' );
	}

	function provideValidConstructionValues(){
		return array(
			array( array( 'localhost', null , null ) ),
			array( array( 'localhost', $this->getMock('Addframe\Http') , null ) ),
			array( array( 'en.wikipedia.org', $this->getMock('Addframe\Http') , $this->getMockFamilyForConstruction() ) ),
		);
	}

	function getMockFamilyForConstruction(){
		$family = $this->getMock( 'Addframe\Family', array('getSiteDetailsFromSiteIndex') );
		$family->expects( $this->any() )->
			method( 'getSiteDetailsFromSiteIndex' )->
			will( $this->returnValue( array('lang' => 'en', 'code' => 'wiki') ) );
		return $family;
	}

	/**
	 * @dataProvider provideInvalidConstructionValues
	 */
	function testCanNotConstructFamilyWithEmptyUrl( $values  ){
		$this->setExpectedException('Exception', 'Can not construct a site without a url');
		new Site( $values[0], $values[1] );
	}

	function provideInvalidConstructionValues(){
		return array(
			array( array( '', null , null ) ),
			array( array( '', $this->getMockHttp() , null ) ),
			array( array( '', $this->getMockHttp() ,$this->getMockFamilyForConstruction() ) ),
			array( array( '', null ,$this->getMockFamilyForConstruction() ) ),
		);
	}

	/**
	 * @dataProvider provideRequestApiUrlData
	 */
	function testGetApiUrl( $values ){
		$site = new Site( 'localhost', $this->getMockHttp( $values[1] ) );
		$this->assertEquals( $values[0], $site->getApiUrl() );
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
			$toReturn[] = array ( array( $apiUrl, array( 0 => $before.$apiUrl.$after ) ) );
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