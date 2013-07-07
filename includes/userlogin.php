<?php

/**
 * This class is designed to represet a User with login details
 * @author Addshore
 **/

class UserLogin extends User {

	private $password;

	/**
	 * @param $handel string for associated site
	 * @param $username string Username for login
	 * @param $password string Password for login
	 */
	function __construct( $handel, $username, $password ) {
		$this->handel = $handel;
		$this->username = $username;
		$this->password = $password;
	}

	/**
	 * @return string Password for login
	 */
	public function getPassword() {
		return $this->password;
	}

	/**
	 * @return Page a sandbox that can be used during testing
	 */
	function getSandbox(){
		return new Page($this->handel,"User:$this->username/Sandbox");
	}

}