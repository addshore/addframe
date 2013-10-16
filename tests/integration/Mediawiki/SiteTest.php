<?php

namespace Addframe\Test;

use Addframe\Http;
use Addframe\Mediawiki\Site;

class SiteTest extends \PHPUnit_Framework_TestCase {

	public function testSite( ) {
		$site = Site::newFromUrl( SITEURL );
		//TODO FIXME siteurl has protocol, apiurl does not..
		$this->assertEquals( str_replace( 'http://', '', SITEURL ) , $site->getUrl() );
		$this->assertEquals( 'http://localhost/wiki/api.php', $site->getApi()->getUrl() );
		//TODO
	}

}