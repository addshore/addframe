<?php

namespace Addframe\Mediawiki\Tests;
use Addframe\Mediawiki\Title;

/**
 *
 * @since 0.0.4
 *
 * @author Addshore
 */

class TitleTest extends MediawikiTestCase {

	/**
	 * @dataProvider provideValidTitleValues
	 */
	function testCanConstructFamily( $title ){
		new Title( $title , $this->getMockPage() );
		$this->assertTrue( true, 'Unable to construct a Title object' );
	}

	function provideValidTitleValues(){
		return array(
			array(  'A Title!' ),
			array(  'Another_Title!' ),
			array(  'Project:Project Page' ),
			array(  'User talk:SomeUser3456789' ),
		);
	}
}