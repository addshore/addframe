<?php

namespace Addframe\Mediawiki\Tests;
use Addframe\Mediawiki\Page;
use Addframe\Mediawiki\PageList;

/**
 * @covers Addframe\Mediawiki\PageList
 *
 * @since 0.0.2
 *
 * @author Addshore
 */

class PageListTest extends MediawikiTestCase {

	/**
	 * @dataProvider provideValidConstructionValues
	 */
	function testCanConstructFamily( $value, $count ){
		$pageList = new PageList( $value );

		$this->assertEquals( $count , count( $pageList ), "Number of items in list were not equal" );

		if( count( $pageList ) > 0 ) {
			if( $value instanceof Page ){
				$this->assertEquals( $value, $pageList->offsetGet( 0 ), 'Could not assert all pages were added to list during construction' );
			} else if ( is_array( $value ) ) {
				foreach( $value as $key => $page ){
					$this->assertEquals( $page, $pageList->offsetGet( $key ), 'Could not assert pages were added correctly during construction');
				}
			}
		}

	}

	function provideValidConstructionValues(){
		return array(
			array( 'This is a string' , 0 ),
			array( null , 0 ),
			array( $this->getMockPage(), 1 ),
			array( array ( $this->getMockPage(), $this->getMockPage() ), 2 ),
		);
	}

	function testCanAppendArray(){
		$pageList = new PageList();
		$pageList->appendArray( array ( $this->getMockPage(), $this->getMockPage() ) );
		$this->assertEquals( 2 , count( $pageList ), 'Could not assert all pages were added to list');
		$this->assertEquals( $this->getMockPage(), $pageList->offsetGet( 0 ) );
		$this->assertEquals( $this->getMockPage(), $pageList->offsetGet( 1 ) );
	}

	function testCanMakeUniqueUsingPageDetails(){
		$pageList = new PageList( array(
			$this->getMockPage( array( 'lang' => 'en', 'type' => 'wiki' ), 'Wikipedia:Sandbox' ),
			$this->getMockPage( array( 'lang' => 'en', 'type' => 'wiki' ), 'Wikipedia:Sandbox' ),
			$this->getMockPage( array( 'lang' => 'de', 'type' => 'wiki' ), 'Berlin' ),
			$this->getMockPage( array( 'lang' => 'de', 'type' => 'wikivoyage' ), 'Berlin' ),
			$this->getMockPage( array( 'lang' => 'de', 'type' => 'wikivoyage' ), 'Berlin' ),
			$this->getMockPage( array( 'lang' => 'de', 'type' => 'wikivoyage' ), 'Germany' ),
		) );
		$pageList->makeUniqueUsingPageDetails();
		$this->assertEquals( 4 , count( $pageList ));
	}

	function testCanMakeUniqueUsingSiteDetails(){
		$pageList = new PageList( array(
			$this->getMockPage( array( 'lang' => 'en', 'type' => 'wiki' ), 'Wikipedia:Sandbox' ),
			$this->getMockPage( array( 'lang' => 'en', 'type' => 'wiki' ), 'Wikipedia:Sandbox' ),
			$this->getMockPage( array( 'lang' => 'de', 'type' => 'wiki' ), 'Berlin' ),
			$this->getMockPage( array( 'lang' => 'de', 'type' => 'wikivoyage' ), 'Berlin' ),
			$this->getMockPage( array( 'lang' => 'de', 'type' => 'wikivoyage' ), 'Berlin' ),
			$this->getMockPage( array( 'lang' => 'de', 'type' => 'wikivoyage' ), 'Germany' ),
		) );
		$pageList->makeUniqueUsingSiteDetails();
		$this->assertEquals( 3 , count( $pageList ));
	}

}