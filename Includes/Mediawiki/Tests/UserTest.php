<?php

namespace Addframe\Tests;
use Addframe\Mediawiki\User;


/**
 * @covers Addframe\Mediawiki\User
 *
 * @since 0.0.2
 *
 * @author Addshore
 * @author Legoktm
 */

/**
 * So we can hijack and set our own custom info
 */
class TestUser extends User {
	public function setUserInfo( $data ) {
		$this->userinfo = $data;
	}
}

class UserTest extends \PHPUnit_Framework_TestCase {

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
		$user = new User( $site, $username );
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

	public static function provideRandomNumbers() {
		$arr = array();
		while ( count( $arr ) < 50 ) {
			$arr[] = array( rand(1, 100000000) );
		}
		return $arr;
	}

	/**
	 * @dataProvider provideRandomNumbers
	 * @param int $count
	 */
	function testRequestEditCount( $count ) {
		// inject it
		$user = new TestUser( $this->getMockSite(), 'Username' );
		$user->setUserInfo( array( 'editcount' => $count ) );
		$this->assertEquals( $count, $user->requestEditcount() );

	}

}