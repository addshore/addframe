<?php

/**
 * This class is designed to represet a Mediawiki User
 * @author Addshore
 **/

class User {

	/**
	 * @var string siteUrl for associated site
	 */
	public $siteUrl;
	/**
	 * @var string username
	 */
	public $username;
	/**
	 * @var string userid
	 */
	public $userid;
	/**
	 * @var array userrights
	 */
	public $rights;
	/**
	 * @var string editcount
	 */
	public $editcount;
	/**
	 * @var string timestamp we got the editcount
	 */
	public $editcounttime;

	/**
	 * @param $siteUrl
	 * @param $username
	 */
	function __construct( $siteUrl, $username ) {
		$this->siteUrl = $siteUrl;
		$this->username = $username;
	}

	/**
	 * @return Page object for this users page
	 */
	function getUserPage(){
		return new Page($this->siteUrl,"User:$this->username");
	}

	/**
	 * @return Page object for this users talk pages
	 */
	function getUserTalkPage(){
		return new Page($this->siteUrl,"User talk:$this->username");
	}

	/**
	 * Gets a users rights
	 * @return mixed
	 */
	function getRights(){
		$param['auprop'] = 'rights';
		$param['aufrom'] = $this->username;
		$param['aulimit'] = '1';
		$result = Globals::$Sites->getSite($this->siteUrl)->api->doListAllusers($param);
		$this->rights = $result->value['query']['allusers']['0']['rights'];
		$this->userid = $result->value['query']['allusers']['0']['userid'];
		return $this->rights;
	}

	/**
	 * gets a users edit count
	 * @return mixed
	 */
	function getEditcount(){
		$param['auprop'] = 'editcount';
		$param['aufrom'] = $this->username;
		$param['aulimit'] = '1';
		$result = Globals::$Sites->getSite($this->siteUrl)->api->doListAllusers($param);
		$this->editcount = $result->value['query']['allusers']['0']['editcount'];
		$this->userid = $result->value['query']['allusers']['0']['userid'];
		$this->editcounttime = time();
		return $this->editcount;
	}

}