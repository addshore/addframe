<?php

namespace Addframe\Test;

use Addframe\Mediawiki\Site;
use Addframe\Mediawiki\TestApi;
use Addframe\Mediawiki\User;

/**
 * Class SiteTest
 * @covers Addframe\Mediawiki\User
 */
class UserTest extends MediawikiTestCase {

	function testNewFromName(){
		$user = User::newFromUsername( 'Foo' );
		$this->assertInstanceOf( 'Addframe\Mediawiki\User', $user );
		$this->assertEquals( $user->getName(), 'Foo' );
	}

	function testNewFromNameWithSite(){
		$site = new Site();
		$site->setUrl( 'foourl' );
		$user = User::newFromUsername( 'Foo', $site );
		$this->assertInstanceOf( 'Addframe\Mediawiki\User', $user );
		$this->assertEquals( $user->getName(), 'Foo' );
		$this->assertEquals( $user->getSite(), $site );
	}

	function testLoadWithNoSite(){
		$user = User::newFromUsername( 'Admin' );
		$this->assertFalse( $user->load() );
	}

	function testLoadWithBadSite(){
		$this->setExpectedException( '\UnexpectedValueException' );
		User::newFromUsername( 'Admin', 'foo' );
	}

	function provideAdminUserWithSite(){
		$site = new Site();
		$api = new TestApi( $this->getTestApiData( 'users/Admin.json' ) );
		$site->setApi( $api );
		$user = User::newFromUsername( 'Admin', $site );
		return $user;
	}

	function testLoadWithSite(){
		$user = $this->provideAdminUserWithSite();

		$this->assertTrue( $user->load() );

		$this->assertEquals( 'Admin', $user->getName() );
		$this->assertEquals( 'unknown', $user->getGender() );
		$this->assertEquals( 39, $user->getEditcount() );
		$this->assertEquals( '2013-08-23T09:49:00Z', $user->getRegistration() );
		$this->assertEquals( 1, $user->getId() );
		$this->assertTrue( is_array( $user->getImplicitgroups() ) );
		$this->assertTrue( is_array( $user->getGroups() ) );
		$this->assertTrue( is_array( $user->getRights() ) );

	}

	function testGetName(){
		$user = $this->provideAdminUserWithSite();
		$this->assertEquals( 'Admin', $user->getName() );
	}

	function testGetGender(){
		$user = $this->provideAdminUserWithSite();
		$this->assertEquals( 'unknown', $user->getGender() );
	}

	function testGetEditcount(){
		$user = $this->provideAdminUserWithSite();
		$this->assertEquals( 39, $user->getEditcount() );
	}

	function testGetRegistration(){
		$user = $this->provideAdminUserWithSite();
		$this->assertEquals( '2013-08-23T09:49:00Z', $user->getRegistration() );
	}

	function testGetId(){
		$user = $this->provideAdminUserWithSite();
		$this->assertEquals( 1, $user->getId() );
	}

	function testGetImplicitgroups(){
		$user = $this->provideAdminUserWithSite();
		$this->assertTrue( is_array( $user->getImplicitgroups() ) );
	}

	function testGetGroups(){
		$user = $this->provideAdminUserWithSite();
		$this->assertTrue( is_array( $user->getGroups() ) );
	}

	function testGetRights(){
		$user = $this->provideAdminUserWithSite();
		$this->assertTrue( is_array( $user->getRights() ) );
	}

	function testGetUserPage(){
		$user = $this->provideAdminUserWithSite();
		$page = $user->getUserPage();
		$this->assertInstanceOf( 'Addframe\Mediawiki\Page', $page );
		$this->assertEquals( 'User:Admin', $page->getTitle() );
	}

	function testGetUserTalkPage(){
		$user = $this->provideAdminUserWithSite();
		$page = $user->getUserTalkPage();
		$this->assertInstanceOf( 'Addframe\Mediawiki\Page', $page );
		$this->assertEquals( 'User talk:Admin', $page->getTitle() );
	}
}