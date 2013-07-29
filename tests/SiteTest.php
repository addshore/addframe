<?php

namespace Addframe\Tests;

use Addframe\Site;

class SiteTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @dataProvider provideValidConstructionValues
	 */
	function testCanConstructFamily( ){
		new Site('localhost');
		$this->assertTrue( true, 'Unable to construct a Site object with a url' );
	}

	function provideValidConstructionValues(){
		return array(
			array( array( 'localhost', null ) ),
			array( array( 'en.wikipedia.org', $this->provideMockFamily() ) ),
		);
	}

	function provideMockFamily(){
		$family = $this->getMock( 'Addframe\Family', array('getSiteDetailsFromSiteIndex') );
		$family->expects( $this->any())->
			method( 'getSiteDetailsFromSiteIndex' )->
			will( $this->returnValue( array('lang' => 'en', 'code' => 'wiki') ) );
		return $family;
	}

	function testCanNotConstructFamilyWithEmptyUrl( ){
		$this->setExpectedException('Exception', 'Can not construct a site without a url');
		new Site('');
	}

}