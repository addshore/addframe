<?php
namespace Addframe\Mediawiki;

use Addframe\Mediawiki\Site;
use Addframe\Mediawiki\Page;

/**
 * @since 0.0.4
 * @author legoktm
 */

class PageTest extends \PHPUnit_Framework_TestCase {

	/** @var Site */
	protected $site;

	protected function setUp() {
		$this->site = new Site( 'en.wikipedia.org' );
		parent::setUp();
	}

	public static function provideSampleTitles() {
		return array(
			array( 'Test' ),
			array( 'Main Page' ),
			array( 'User:Test' ),
			array( 'Template:Blah' ),
			array( 'Talk:Foo' ),
		);
	}

	/**
	 * @param $title string
	 * @dataProvider provideSampleTitles
	 */
	public function testConstructor( $title ) {
		$pg = new Page( $this->site, $title );
		$this->assertTrue( true );
		$this->assertEquals( $pg->getTitle(), $title );
	}

	public static function provideIds() {
		return array(
			array( 'Test', 11089416 ),
			array( 'Main Page', 15580374 ),
			array( 'User:Test', 203285 ),
			array( 'Template:Cite web', 4148498 ),
			array( 'Talk:Foo', 9132809 ),
		);
	}

	/**
	 * @dataProvider provideIds
	 * @param $title string
	 * @param $id int
	 */
	public function testId( $title, $id ) {
		$pg = new Page( $this->site, $title );
		$this->assertEquals( $pg->getId(), $id );
	}

	public static function provideNS() {
		return array(
			array( 'Test', 0 ),
			array( 'Main Page', 0 ),
			array( 'User:Test', 2 ),
			array( 'Template:Cite web', 10 ),
			array( 'Talk:Foo', 1 ),
		);
	}

	/**
	 * @dataProvider provideNS
	 * @param $title string
	 * @param $ns int
	 */
	public function testNamespace( $title, $ns ) {
		$pg = new Page( $this->site, $title );
		$this->assertEquals( $pg->getNamespace(), $ns );
	}

	public static function provideToggleTalkPage() {
		return array(
			array( 'User:Blah', 'User talk:Blah' ),
			array( 'Main Page', 'Talk:Main Page' ),
			array( 'MediaWiki:Omg', 'MediaWiki talk:Omg' ),
			array( 'Special:Watchlist', 'Special:Watchlist' ),
		);
	}

	/**
	 * @dataProvider provideToggleTalkPage
	 * @param $first string
	 * @param $second string
	 */
	public function testToggleTalkPage( $first, $second ) {
		$pg = new Page( $this->site, $first );
		$pg2 = new Page( $this->site, $second );
		$this->assertEquals( $pg->toggleTalkPage()->getTitle(), $pg2->getTitle() );
		$this->assertEquals( $pg2->toggleTalkPage()->getTitle(), $pg->getTitle() );
	}


}
