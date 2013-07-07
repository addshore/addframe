<?php

/**
 * This class is designed to represet a Mediawiki User
 * @author Addshore
 **/

class User {

	/**
	 * @var string handel for associated site
	 */
	public $handel;
	public $username;

	function __construct( $handel, $username ) {
		$this->handel = $handel;
		$this->username = $username;
	}

	function getUserPage(){
		return new Page($this->handel,"User:$this->username");
	}

	function getUserTalkPage(){
		return new Page($this->handel,"User talk:$this->username");
	}

}