<?php

namespace Addframe;

/**
 * This class is designed to represet a Site User
 * @author Addshore
 **/

class User {

	/**
	 * @var Site
	 */
	public $site;
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
	 * @param $site
	 * @param $username
	 */
	public function __construct( $site, $username ) {
		$this->site = $site;
		$this->username = $username;
	}

	/**
	 * @return Page object for this users page
	 */
	public function getUserPage() {
		return new Page( $this->site, "User:$this->username" );
	}

	/**
	 * @return Page object for this users talk pages
	 */
	public function getUserTalkPage() {
		return new Page( $this->site, "User talk:$this->username" );
	}

	/**
	 * Gets a users rights
	 * @return mixed
	 */
	public function requestRights() {
		$param['auprop'] = 'rights';
		$param['aufrom'] = $this->username;
		$param['aulimit'] = '1';
		$result = $this->site->requestListAllusers( $param );
		$this->rights = $result->value['query']['allusers']['0']['rights'];
		$this->userid = $result->value['query']['allusers']['0']['userid'];
		return $this->rights;
	}

	/**
	 * gets a users edit count
	 * @return mixed
	 */
	public function requestEditcount() {
		$param['auprop'] = 'editcount';
		$param['aufrom'] = $this->username;
		$param['aulimit'] = '1';
		$result = $this->site->requestListAllusers( $param );
		$this->editcount = $result->value['query']['allusers']['0']['editcount'];
		$this->userid = $result->value['query']['allusers']['0']['userid'];
		$this->editcounttime = time();
		return $this->editcount;
	}

}