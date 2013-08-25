<?php
use Addframe\Mediawiki\Site;
use Addframe\Mediawiki\SiteList;

/**
 * Tests for the SiteList class.
 * @author Addshore
 * @covers Addframe\Mediawiki\SiteList
 */
class SiteListTest extends PHPUnit_Framework_TestCase {

	public function testGetObjectType() {
		$sites = new SiteList();
		$this->assertEquals( 'Addframe\Mediawiki\Site', $sites->getObjectType() );
	}

	public function testHasValidType() {
		$site = new Site();
		$sites = new SiteList();
		$this->assertTrue(
			$sites->hasValidType( $site ),
			get_class( $site ) . ' is of wrong class, expecting '.$sites->getObjectType() );
	}

	public function testSiteList(){
		$sites = new SiteList();

		$siteOne = Site::newFromUrl( 'localhost' );
		$siteTwo = Site::newFromUrl( 'http://en.wikipedia.org/wiki' );

		$this->assertFalse( $sites->hasSite( $siteOne->getUrl() ) );
		$this->assertTrue( $sites->isEmpty() );
		$this->assertEquals( 0, count( $sites ) );

		$sites->append( $siteOne );

		$this->assertTrue( $sites->hasSite( $siteOne->getUrl() ) );
		$this->assertFalse( $sites->isEmpty() );
		$this->assertEquals( 1, count( $sites ) );

		$sites->append( $siteTwo );

		$this->assertTrue( $sites->hasSite( $siteOne->getUrl() ) );
		$this->assertTrue( $sites->hasSite( $siteTwo->getUrl() ) );
		$this->assertFalse( $sites->isEmpty() );
		$this->assertEquals( 2, count( $sites ) );

		$siteFromList = $sites->getSite( 'localhost' );
		$this->assertInstanceOf( 'Addframe\Mediawiki\Site', $siteFromList );
		$this->assertEquals( $siteOne, $siteFromList );

		$sites->removeSite( 'localhost' );
		$this->assertFalse( $sites->isEmpty() );
		$this->assertEquals( 1, count( $sites ) );

		$sites->removeSite( 'http://en.wikipedia.org/wiki' );
		$this->assertTrue( $sites->isEmpty() );
		$this->assertEquals( 0, count( $sites ) );
	}

}
