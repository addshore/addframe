<?php

class user {

	public $username;

	function __construct( $username ) {
		$this->username = $username;
	}

	function getUserPageTitle()
	{
		return "User:".$this->username;
	}

	function getUserTalkPageTitle()
	{
		return "User talk:".$this->username;
	}

}