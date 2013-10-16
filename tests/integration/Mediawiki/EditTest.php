<?php

namespace Addframe\Test\Integration;

use Addframe\Http;
use Addframe\Mediawiki\Page;

class EditTest extends MediawikiTestCase {

	public function testEdit() {
		$site = $this->newSite();
		$name = 'AF_' . date( 'Y-m-d H:i:s' );
		$page = Page::newFromTitle( $name, $site );
		$this->assertEquals( $site, $page->getSite() );
		$this->assertEquals( $name, $page->getTitle() );
		$this->assertEquals( '', $page->getTouched() );
		//TODO
	}


}