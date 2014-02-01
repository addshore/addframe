<?php

namespace Addframe\Test\Integration;

use Addframe\Mediawiki\Revision;

/**
 * @group medium
 */
class EditTest extends MediawikiTestCase {

	public function testEdit() {
		$site = $this->newSite();
		$name = PAGEPREFIX . 'ET';
		$page = $site->getPage( $name );

		$this->assertEquals( $site, $page->getSite() );
		$this->assertEquals( $name, $page->getTitle() );
		$this->assertTrue( $page->isMissing() );

		$revision = $page->getCurrentRevision();
		$this->assertInstanceOf( 'Addframe\Mediawiki\Revision', $revision );
		$this->assertTrue( $revision->isMissing() );

		$revision = Revision::newBasedOnRevision( $revision );
		$this->assertInstanceOf( 'Addframe\Mediawiki\NewRevision', $revision );

		$revision->content = 'some new content';
		$revision->comment = 'A new page';
		$page->saveNewRevision( $revision );
		$this->assertEquals( 'some new content', $page->getCurrentRevision()->getContent() );
		$this->assertEquals( $revision, $page->getCurrentRevision() );

		$revision = Revision::newBasedOnRevision( $revision );
		$this->assertInstanceOf( 'Addframe\Mediawiki\NewRevision', $revision );
		$revision->content = 'some even newer content!';
		$revision->comment = 'A new revision' ;
		$page->saveNewRevision( $revision );
		$this->assertEquals( 'some even newer content!', $page->getCurrentRevision()->getContent() );
		$this->assertEquals( $revision, $page->getCurrentRevision() );

		$revision = Revision::newBasedOnRevision( $revision );
		$this->assertInstanceOf( 'Addframe\Mediawiki\NewRevision', $revision );
		$revision->content = '' ;
		$revision->comment = 'Blank!' ;
		$page->saveNewRevision( $revision );
		$this->assertEquals( '', $page->getCurrentRevision()->getContent() );
		$this->assertEquals( $revision, $page->getCurrentRevision() );
	}


}