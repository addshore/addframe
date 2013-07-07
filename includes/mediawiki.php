<?php

/**
 * This class is designed to represent a mediawiki installation
 * @author Addshore
 **/

class Mediawiki {
	public $handel;
	/**
	 * @var string Hostname of Mediawiki site
	 */
	public $hostname;
	/**
	 * @var MediawikiAPI Location in relation to hostname of api.php
	 */
	public $api;
	/**
	 * @var UserLogin
	 */
	public $userlogin;

	/**
	 * @param $hostname string Hostname of Mediawiki site
	 * @param null $api Location in relation to hostname of api.php
	 */
	function __construct ($handel, $hostname,$api = null) {
		$this->handel = $handel;
		$this->hostname = $hostname;
		if(isset($api)){
			$this->api = new MediawikiAPI($this->hostname.$api);
		}
	}

	/**
	 * Creates a page item from a pagetitle for the site
	 * @param $page
	 * @return Page
	 */
	function getPage ($page) {
		return new Page($this->handel,$page);
	}

	/**
	 * Creates a user item from a username for the site
	 * @param $username
	 * @return User
	 */
	function getUser ($username) {
		return new User($this->handel,$username);
	}

	/**
	 * Creates a entity item from a entityid for the site
	 * @param $id
	 * @return WikibaseEntity
	 */
	function getEntity ($id) {
		return new WikibaseEntity($this->handel,$id);
	}

	/**
	 * @return bool
	 * @throws Exception
	 */
	function doLogin () {

		$post['lgname'] = $this->userlogin->username;
		$post['lgpassword'] = $this->userlogin->getPassword();

		$result = $this->api->doLogin(null,$post);

		if ($result->statusCode == 'NeedToken') {
			$post['lgtoken'] = $result->getInside()['token'];
			$result = $this->api->doLogin(null,$post);
		}

		if ($result->statusCode == "Success") {
			print "Log in: ".$result->statusCode."\n";
			return true;
		}
		else{
			throw new Exception('Failed login');
		}
	}

	/**
	 * @param $title string Title to be edited
	 * @param $text string Text to be placed
	 * @param null $summary Edit Summary
	 * @param bool $minor Do we want to mark the edit as minor?
	 * @return string
	 */
	function doEdit ($title,$text,$summary = null, $minor = false) {

		$param['title'] = $title;
		$param['text'] = $text;
		if( isset($summary) ) { $param['summary'] = $summary; }
		if( $minor == true ) { $param['minor'] = '1'; }

		$result = $this->api->doEdit($param);

		print "Edit: ".$result->statusCode."\n";
		return $result;
	}
	
}