<?php
namespace Addframe\Mediawiki;
use Addframe\Mediawiki\Site;
use Addframe\Mediawiki\Page;



class PageTest extends \PHPUnit_Framework_TestCase {

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

}
