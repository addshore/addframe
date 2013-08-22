<?php

namespace Addframe\Test;

use Addframe\Wiki\Site;
use PHPUnit_Framework_TestCase;

class SiteTest extends PHPUnit_Framework_TestCase {

	 function testCanConstruct(){
		 new Site();
		 $this->assertTrue( true );
	 }

	function testCanSetUrl(){
		$site = new Site();
		$site->setUrl( "en.wikipedia.org" );
		$this->assertEquals( "en.wikipedia.org", $site->getUrl() );
	}

}