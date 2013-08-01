<?php

namespace Addframe\Tests;
use Addframe\UserLogin;


/**
 * @covers Addframe\UserLogin
 *
 * @since 0.0.2
 *
 * @author Addshore
 */

class UserLoginTest extends \PHPUnit_Framework_TestCase {

	function provideLoginDetails(){
		return array(
			array( 'username', 'password' ),
			array( 'DifferentUsername', 'F6"80( dHi#]d_?/.s##' ),
		);
	}

	/**
	 * @dataProvider provideLoginDetails
	 */
	function testValidConstruction( $username, $password ){
		new UserLogin( $username, $password );
		$this->assertTrue( true );
	}

	/**
	 * @dataProvider provideLoginDetails
	 */
	function testCanGetPassword( $username, $password ){
		$userLogin = new UserLogin( $username, $password );
		$this->assertEquals( $password, $userLogin->getPassword(), 'Could not assert password was correctly set');

	}

}