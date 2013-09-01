<?php

use Addframe\Mediawiki\Page;
use Addframe\Mediawiki\Site;
use Addframe\Mediawiki\TestApi;

/**
 * @covers Addframe\Mediawiki\Page
 */
class PageTest extends MediawikiTestCase{

	function testNewFromTitle(){
		$page = Page::newFromTitle( 'Foo' );
		$this->assertInstanceOf( 'Addframe\Mediawiki\Page', $page );
		$this->assertEquals( $page->getTitle(), 'Foo' );
	}

	function testNewFromTitleWithSite(){
		$site = new Site();
		$site->setUrl( 'foourl' );
		$page = Page::newFromTitle( 'Foo', $site );
		$this->assertInstanceOf( 'Addframe\Mediawiki\Page', $page );
		$this->assertEquals( $page->getTitle(), 'Foo' );
		$this->assertEquals( $page->getSite(), $site );
	}

	function testLoadWithNoSite(){
		$page = Page::newFromTitle( 'Foo' );
		$this->assertFalse( $page->load() );
	}

	function testLoadWithBadSite(){
		$this->setExpectedException( '\UnexpectedValueException' );
		$page = Page::newFromTitle( 'Admin', 'foo');
	}

	function provideFooPageWithSite(){
		$site = new Site();
		$api = new TestApi( $this->getTestApiData( 'query/pages/Foo.json' ) );
		$site->setApi( $api );
		$page = Page::newFromTitle( 'Foo', $site );
		return $page;
	}

	function testLoadWithSite(){
		$page = $this->provideFooPageWithSite();

		$this->assertTrue( $page->load() );

		$this->assertEquals( 'Foo', $page->getTitle() );
		$this->assertEquals( 0, $page->getNs() );
		$this->assertEquals( 4, $page->getId() );
		$this->assertEquals( 'wikitext', $page->getContentmodel() );
		$this->assertEquals( 3, $page->getCounter() );
		$this->assertEquals( 'Foo', $page->getDisplaytitle() );
		$this->assertEquals( 87, $page->getLastrevid() );
		$this->assertEquals( 13, $page->getLength() );
		$this->assertEquals( 'en', $page->getPagelanguage() );
		$this->assertEquals( array(), $page->getProtection() );
		$this->assertEquals( '2013-09-01T09:46:15Z', $page->getTouched() );
	}

	function testGetNs(){
		$page = $this->provideFooPageWithSite();
		$this->assertEquals( 0, $page->getNs() );
	}

	function testGetId(){
		$page = $this->provideFooPageWithSite();
		$this->assertEquals( 4, $page->getId() );
	}

	function testGetContentmodel(){
		$page = $this->provideFooPageWithSite();
		$this->assertEquals( 'wikitext', $page->getContentmodel() );
	}

	function testGetCounter(){
		$page = $this->provideFooPageWithSite();
		$this->assertEquals( 3, $page->getCounter() );
	}

	function testGetDisplaytitle(){
		$page = $this->provideFooPageWithSite();
		$this->assertEquals( 'Foo', $page->getDisplaytitle() );
	}

	function testGetLastrevid(){
		$page = $this->provideFooPageWithSite();
		$this->assertEquals( 87, $page->getLastrevid() );
	}

	function testGetLength(){
		$page = $this->provideFooPageWithSite();
		$this->assertEquals( 13, $page->getLength() );
	}

	function testGetPagelanguage(){
		$page = $this->provideFooPageWithSite();
		$this->assertEquals( 'en', $page->getPagelanguage() );
	}

	function testGetProtection(){
		$page = $this->provideFooPageWithSite();
		$this->assertEquals( array(), $page->getProtection() );
	}

	function testGetTouched(){
		$page = $this->provideFooPageWithSite();
		$this->assertEquals( '2013-09-01T09:46:15Z', $page->getTouched() );
	}

}