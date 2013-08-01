<?php

namespace Addframe;

/**
 * This class is designed to represet a User with login details
 * @author Addshore
 **/

class UserLogin extends User {

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

	/**
	 * @return Page a sandbox that can be used during testing
	 */
	public function newSandboxPage() {
		return new Page( $this->site, "User:$this->username/Sandbox" );
	}

}