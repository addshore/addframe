<?php

namespace Addframe\Mediawiki\Tests;
use Addframe\Mediawiki\User;


/**
 * @covers Addframe\Mediawiki\User
 *
 * @since 0.0.2
 *
 * @author Addshore
 */

class UserLogin extends MediawikiTestBase {

	function provideUserDetails(){
		return array(
			array( 'username', $this->getMockSite() ),
			array( 'DifferentUsername', $this->getMockSite() ),
		);
	}

	/**
	 * @dataProvider provideUserDetails
	 */
	function testValidConstruction( $username, $site ){
		new User( $site,$username );
		$this->assertTrue( true );
	}

	/**
	 * @dataProvider provideUserDetails
	 */
	function testGetUserPage( $username, $site ){
		$user = new User( $site,$username );
		$userPage = $user->getUserPage();
		$this->assertInstanceOf( 'Addframe\Mediawiki\Page', $userPage , 'Did not return instance of Page' );
		$this->assertEquals( 'User:'.$username, $userPage->title->getTitle(), 'Did not set the correct Page Title' );
	}

	/**
	 * @dataProvider provideUserDetails
	 */
	function testGetUserTalkPage( $username, $site ){
		$user = new User( $site,$username );
		$userTalkPage = $user->getUserTalkPage();
		$this->assertInstanceOf( 'Addframe\Mediawiki\Page', $userTalkPage , 'Did not return instance of Page' );
		$this->assertEquals( 'User talk:'.$username, $userTalkPage->title->getTitle(), 'Did not set the correct Page Title' );
	}

}