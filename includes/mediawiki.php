<?php

/**
 * This class is designed to represet a medawiki instalation
 * @author Addshore
 **/

class mediawiki {
	/**
	 * @var string Hostname of mediawiki site
	 */
	public $hostname;
	/**
	 * @var mediawikiApi Location in relation to hostname of api.php
	 */
	public $api;

	/**
	 * @var userlogin
	 */
	public $userlogin;

	/**
	 * @param $hostname string Hostname of mediawiki site
	 * @param null $api Location in relation to hostname of api.php
	 */
	function __construct ($hostname,$api = null) {
		$this->hostname = $hostname;
		if(isset($api)){
			$this->api = new mediawikiApi($this->hostname.$api);
		}
	}

	/**
	 * @param userlogin $userLogin
	 * @return bool
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


	function getPageText ($page) {
		$param['titles'] = $page;
		$result = $this->api->doPropRevsions($param);
		return $result;
	}
	
}