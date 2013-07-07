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

		$apiresult = $this->api->doLogin(null,$post);
		$result = $this->api->parseReturned( $apiresult );

		if ($result == 'NeedToken') {
			$post['lgtoken'] = $apiresult['login']['token'];
			$apiresult = $this->api->doLogin(null,$post);
			$result = $this->api->parseReturned( $apiresult );
		}

		if ($result == "Success") {
			print "Log in: $result\n";
			return true;
		}
		else{
			print_r($apiresult);
			die();
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

		$post['title'] = $title;
		$post['text'] = $text;
		if( isset($summary) ) { $post['summary'] = $summary; }
		if( $minor == true ) { $post['minor'] = '1'; }

		$result = $this->api->doEdit($post);

		print "Edit: $result\n";
		return $result;
	}
	
}