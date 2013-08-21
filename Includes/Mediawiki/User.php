<?php

namespace Addframe\Mediawiki;

/**
 * This class is designed to represent a Site User
 * @since 0.0.1
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
	 * @var array
	 */
	protected $userinfo = null;

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
		return new Page( $this->site, "User:$this->username", 2 );
	}

	/**
	 * @return Page object for this users talk pages
	 */
	public function getUserTalkPage() {
		return new Page( $this->site, "User talk:$this->username" );
	}

	/**
	 * Fetches all the info we can from list=users and stores it
	 * @return array
	 */
	public function fetchUserInfo() {
		if ( !is_null( $this->userinfo ) ) {
			return $this->userinfo;
		}
		$params['ususers'] = $this->username;
		$params['usprop'] = 'emailable|blockinfo|groups|implicitgroups|rights|editcount|registration|emailable|gender';
		$params['uslimit'] = '1';
		$result = $this->site->api->requestListUsers( $params );
		$this->userinfo = $result['query']['users'][0];
		return $this->userinfo;
	}


	/**
	 * Gets a users rights
	 * @return array
	 */
	public function requestRights() {
		$info = $this->fetchUserInfo();
		$this->rights = $info['rights'];
		return $this->rights;
	}

	/**
	 * gets a users edit count
	 * @return int
	 */
	public function requestEditcount() {
		$info = $this->fetchUserInfo();
		$this->editcount = $info['editcount'];
		$this->editcounttime = time();
		return $this->editcount;
	}

	/**
	 * Check whether we can email a user
	 * @return bool
	 */
	public function hasEmailEnabled() {
		return in_array( "emailable", array_keys( $this->fetchUserInfo() ) );
	}

}
