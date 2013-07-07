<?php

class userlogin extends user {

	public $password;

	function __construct( $username, $password ) {
		$this->username = $username;
		$this->password = $password;
	}

}