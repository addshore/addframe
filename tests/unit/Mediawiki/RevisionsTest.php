<?php

namespace Addframe\Test\Unit;

use Addframe\Mediawiki\Revisions;

/**
 * @covers Addframe\Mediawiki\Revisions
 */
class RevisionsTest extends \PHPUnit_Framework_TestCase {

	public function newMockRevision( $revid ){
		$mock = $this->getMock( 'Addframe\Mediawiki\Revision' );
		$mock->expects( $this->any() )
			->method( 'getRevId' )
			->will( $this->returnValue( $revid ) );
		return $mock;
	}

	public function testCanConstructEmpty(){
		$revs = new Revisions();
		$this->assertEquals( 0, $revs->count() );
	}

	public function testCanConstructWithRevision(){
		$revs = new Revisions( $this->newMockRevision( 123 ) );
		$this->assertEquals( 1, $revs->count() );
		$this->assertTrue( $revs->hasRevisionWithId( 123 ) );
	}

	public function testCanConstructWithArray(){
		$revs = new Revisions( array( $this->newMockRevision( 22 ), $this->newMockRevision( 33 ) ) );
		$this->assertEquals( 2, $revs->count() );
		$this->assertTrue( $revs->hasRevisionWithId( 22 ) );
		$this->assertTrue( $revs->hasRevisionWithId( 33 ) );
	}

}