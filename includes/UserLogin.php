<?php

namespace Addframe;

/**
 * This class is designed to represent a User with login details
 * @since 0.0.1
 * @author Addshore
 **/

class UserLogin {

	protected $password;

	/**
	 * @param $username string Username for login
	 * @param $password string Password for login
	 */
	public function __construct( $username, $password ) {
		$this->username = $username;
		$this->password = $password;
	}

	/**
	 * @return string Password for login
	 */
	public function getPassword() {
		return $this->password;
	}

}