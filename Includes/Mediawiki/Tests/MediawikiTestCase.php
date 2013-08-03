<?php

namespace Addframe\Mediawiki\Tests;

/**
 *
 * @since 0.0.4
 *
 * @author Addshore
 */

class MediawikiTestCase extends \PHPUnit_Framework_TestCase {

	function getMockFamilyForConstruction(){
		$family = $this->getMock( 'Addframe\Mediawiki\Family', array('getSiteDetailsFromSiteIndex') );
		$family->expects( $this->any() )->
			method( 'getSiteDetailsFromSiteIndex' )->
			will( $this->returnValue( array('lang' => 'en', 'code' => 'wiki') ) );
		return $family;
	}

	function getMockSite(){
		$mockSite = $this->getMockBuilder( 'Addframe\Mediawiki\Site' )->disableOriginalConstructor()->getMock();
		return $mockSite;
	}

	function getMockHttp( $requestResult = array( 0 => '' ) ){
		$http = $this->getMock( 'Addframe\Http', array('get','post') );
		foreach( $requestResult as $key => $return ){
			$http->expects( $this->at( $key ) )->method( 'get' )->will( $this->returnValue( $return ) );
			$http->expects( $this->at( $key ) )->method( 'post' )->will( $this->returnValue( $return ) );
		}
		return $http;
	}

	/**
	 * @param null $site array (language,type)
	 * @param null $title string Title
	 * @return \PHPUnit_Framework_MockObject_MockObject|string
	 */
	function getMockPage( $site = null, $title = null ){
		if( $site != null && $title != null ){
			$mockSite = $this->getMockBuilder('Addframe\Mediawiki\Site')->disableOriginalConstructor()->getMock();
			$mockSite->expects( $this->any() )->method( 'getLanguage' )->will( $this->returnValue( $site['lang'] ) );
			$mockSite->expects( $this->any() )->method( 'getType' )->will( $this->returnValue( $site['type'] ) );

			$pageMock = $this->getMock('Addframe\Mediawiki\Page', array( 'getTitle' ), array( $mockSite, $title ) );
			$pageMock->expects( $this->any() )->method( 'getTitle' )->will( $this->returnValue( $title ) );
			return $pageMock;
		}
		return $this->getMockBuilder('Addframe\Mediawiki\Page')->disableOriginalConstructor()->getMock();
	}

}