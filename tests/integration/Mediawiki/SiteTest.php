<?php

namespace Addframe\Test\Integration;

use Addframe\Http;
use Addframe\Mediawiki\Site;

class SiteTest extends MediawikiTestCase {

	public function testUrls() {
		$site = $this->newSite();

		//TODO FIXME siteurl has protocol, apiurl does not..
		$this->assertEquals( str_replace( 'http://', '', SITEURL ) , $site->getUrl(), 'Unexpected Site url' );
		$this->assertEquals( 'http://localhost/wiki/api.php', $site->getApi()->getUrl(), 'Unexpected API url' );
	}

	public function testLoginLogout() {
		//We should NOT use the ->newsite() method here
		$site = Site::newFromUrl( SITEURL );

		$site->logout();
		$this->assertFalse( $site->isLoggedIn(), 'Failed to assert we were logged out to start' );

		$this->assertTrue( $site->login( SITEUSER, SITEPASS ), 'Failed to log into site' );
		$this->assertEquals( SITEUSER, $site->isLoggedIn(), 'Failed to assert we logged into site' );

		$this->assertTrue( $site->logout(), 'Failed to log out of site' );
		$this->assertFalse( $site->isLoggedIn(), 'Failed to assert we logged out of site' );
	}

	//TODO more tests

}