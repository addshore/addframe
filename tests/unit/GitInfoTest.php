<?php

namespace Addframe\Test\Unit;

use Addframe\GitInfo;

/**
 * Class GitInfoTest
 * @covers \Addframe\GitInfo
 */

class GitInfoTest extends DefaultTestCase {

	public function testSinglton(){
		$this->assertInstanceOf( '\Addframe\GitInfo',GitInfo::repo() );
	}

	public function testHash(){
		$this->assertEquals( strlen(sha1('foo')), strlen(GitInfo::headSHA1()) );
	}

	public function testBranch(){
		$this->assertNotEmpty( GitInfo::currentBranch() );
	}

	public function testIsHash(){
		$this->assertTrue( GitInfo::isSHA1( GitInfo::headSHA1() ) );
	}

	public function testDestruct(){
		$this->assertNull( GitInfo::destruct() );
	}

}