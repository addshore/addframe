<?php

/**
 * This class is designed to represet a mediawiki user
 * @author Addshore
 **/

class user {

	public $username;

	function __construct( $username ) {
		$this->username = $username;
	}

	function getUserPageTitle(){
		return "User:".$this->username;
	}

	function getUserTalkPageTitle(){
		return "User talk:".$this->username;
	}

}