<?php

class mediawiki {
	public $hostname;
	public $api;

	function __construct ($hostname,$api = null) {
		$this->hostname = $hostname;
		if(isset($api))
		{
			$this->api = new mediawikiApi($this->hostname.$api);
		}
	}

	/**
	 * @param userlogin $userLogin
	 * @return bool
	 */
	function doLogin (userlogin $userLogin) {

		$post['lgname'] = $userLogin->username;
		$post['lgpassword'] = $userLogin->password;

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

	function doEdit ($title,$text,$summary = null, $minor = null) {

		$post['title'] = $title;
		$post['text'] = $text;
		if( isset($summary) ) { $post['text'] = $text; }
		if( isset($minor) ) { $post['minor'] = $minor; }

		$result = $this->api->doEdit($post);

		print "Edit: $result\n";
		return $result;
	}
	
}