<?php

namespace Addframe\tests;

use Addframe\GitInfo;

/**
 * Class GitInfoTest
 * @covers \Addframe\GitInfo
 */

class GitInfoTest extends \DefaultTestCase {

	function testSinglton(){
		$this->assertInstanceOf( '\Addframe\GitInfo',GitInfo::repo() );
	}

	function testHash(){
		$this->assertEquals( strlen(sha1('foo')), strlen(GitInfo::headSHA1()) );
	}

	function testBranch(){
		$this->assertNotEmpty( GitInfo::currentBranch() );
	}

	function testIsHash(){
		$this->assertTrue( GitInfo::isSHA1( GitInfo::headSHA1() ) );
	}

	function testDestruct(){
		$this->assertNull( GitInfo::destruct() );
	}

}