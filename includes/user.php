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
	public $userid;
	public $rights;
	public $editcount;
	public $editcounttime;

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

	function getRights(){
		$param['auprop'] = 'rights';
		$param['aufrom'] = $this->username;
		$param['aulimit'] = '1';
		$result = Globals::$Sites->getSite($this->handel)->api->doListAllusers($param);
		$this->rights = $result->value['query']['allusers']['0']['rights'];
		$this->userid = $result->value['query']['allusers']['0']['userid'];
		return $this->rights;
	}

	function getEditcount(){
		$param['auprop'] = 'editcount';
		$param['aufrom'] = $this->username;
		$param['aulimit'] = '1';
		$result = Globals::$Sites->getSite($this->handel)->api->doListAllusers($param);
		$this->editcount = $result->value['query']['allusers']['0']['editcount'];
		$this->userid = $result->value['query']['allusers']['0']['userid'];
		$this->editcounttime = time();
		return $this->editcount;
	}

}