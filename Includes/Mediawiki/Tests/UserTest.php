<?php

namespace Addframe\Tests;
use Addframe\Mediawiki\User;


/**
 * @covers Addframe\Mediawiki\User
 *
 * @since 0.0.2
 *
 * @author Addshore
 */

class UserLogin extends \PHPUnit_Framework_TestCase {

	function provideUserDetails(){
		return array(
			array( 'username', $this->getMockSite() ),
			array( 'DifferentUsername', $this->getMockSite() ),
		);
	}

	function getMockSite(){
		$mockSite = $this->getMockBuilder( 'Addframe\Mediawiki\Site' )->disableOriginalConstructor()->getMock();
		return $mockSite;
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
		$this->assertEquals( 'User:'.$username, $userPage->getTitle(), 'Did not set the correct Page Title' );
	}

	/**
	 * @dataProvider provideUserDetails
	 */
	function testGetUserTalkPage( $username, $site ){
		$user = new User( $site,$username );
		$userTalkPage = $user->getUserTalkPage();
		$this->assertInstanceOf( 'Addframe\Mediawiki\Page', $userTalkPage , 'Did not return instance of Page' );
		$this->assertEquals( 'User talk:'.$username, $userTalkPage->getTitle(), 'Did not set the correct Page Title' );
	}

}