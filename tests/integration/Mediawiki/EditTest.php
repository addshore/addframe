<?php

namespace Addframe\Test\Integration;

use Addframe\Http;
use Addframe\Mediawiki\Revision;

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

		$revision = Revision::newFromRevision( $revision );
		$this->assertInstanceOf( 'Addframe\Mediawiki\Revision', $revision );

		$revision->setContent( 'some new content' );
		$revision->setComment( 'A new page' );
		$page->saveNewRevision( $revision );
		$this->assertEquals( 'some new content', $page->getCurrentRevision()->getContent() );
		$this->assertEquals( $revision, $page->getCurrentRevision() );

		$revision = Revision::newFromRevision( $revision );
		$revision->setContent( 'some even newer content!' );
		$revision->setComment( 'A new revision' );
		$page->saveNewRevision( $revision );
		$this->assertEquals( 'some even newer content!', $page->getCurrentRevision()->getContent() );
		$this->assertEquals( $revision, $page->getCurrentRevision() );

		$revision = Revision::newFromRevision( $revision );
		$revision->setContent( '' );
		$revision->setComment( 'Blank!' );
		$page->saveNewRevision( $revision );
		$this->assertEquals( '', $page->getCurrentRevision()->getContent() );
		$this->assertEquals( $revision, $page->getCurrentRevision() );
	}


}