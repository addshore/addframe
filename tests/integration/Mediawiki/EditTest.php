<?php

namespace Addframe\Test\Integration;

use Addframe\Http;

class EditTest extends MediawikiTestCase {

	public function testEdit() {
		$site = $this->newSite();
		$name = PAGEPREFIX . 'ET';
		$page = $site->getPage( $name );

		$this->assertEquals( $site, $page->getSite() );
		$this->assertEquals( $name, $page->getTitle() );
		$this->assertTrue( $page->isMissing() );

		//todo create page
		//todo assert stuff

		//todo edit page
		//todo assert stuff

		//todo blank page
		//todo assert stuff
	}


}