<?php

/**
 * This class is designed to represet a user with login details
 * @author Addshore
 **/

class userlogin extends user {

	private $password;

	/**
	 * @param $username string Username for login
	 * @param $password string Password for login
	 */
	function __construct( $username, $password ) {
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