<?php

/**
 * This class is designed to represet a mediawiki user
 * @author Addshore
 **/

class user {

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
		return new page($this->handel,"User:$this->username");
	}

	function getUserTalkPage(){
		return new page($this->handel,"User talk:$this->username");
	}

}