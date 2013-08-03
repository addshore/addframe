<?php

namespace Addframe\Mediawiki\Tests;
use Addframe\Mediawiki\Family;

/**
 *
 * @since 0.0.2
 *
 * @author Addshore
 */

class FamilyTest extends MediawikiTestBase {

	/**
	 * @dataProvider provideValidConstructionValues
	 */
	function testCanConstructFamily( $login, $homeurl ){
		new Family( $login, $homeurl );
		$this->assertTrue( true, 'Unable to construct a Family object' );
	}

	function provideValidConstructionValues(){
		return array(
			array(  null, null ),
			array(  $this->getMockBuilder( 'Addframe\Mediawiki\UserLogin' )->disableOriginalConstructor()->getMock(), null),
			//@todo test with url
		);
	}
}