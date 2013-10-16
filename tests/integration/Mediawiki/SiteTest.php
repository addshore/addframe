<?php

namespace Addframe\Test;

use Addframe\Http;
use Addframe\Mediawiki\Site;

class SiteTest extends \PHPUnit_Framework_TestCase {

	public function newSite(){
		return Site::newFromUrl( SITEURL );
	}

	public function testUrls( ) {
		$site = $this->newSite();

		//TODO FIXME siteurl has protocol, apiurl does not..
		$this->assertEquals( str_replace( 'http://', '', SITEURL ) , $site->getUrl(), 'Unexpected Site url' );
		$this->assertEquals( 'http://localhost/wiki/api.php', $site->getApi()->getUrl(), 'Unexpected API url' );
	}

	public function testLoginLogout( ) {
		$site = $this->newSite();

		$this->assertTrue( $site->login( SITEUSER, SITEPASS ), 'Failed to log into site' );
		$this->assertTrue( $site->logout() );
	}

	//TODO more tests

}