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

	function getUserPage(){
		return new page("User:$this->username");
	}

	function getUserTalkPage(){
		return new page("User talk:$this->username");
	}

}