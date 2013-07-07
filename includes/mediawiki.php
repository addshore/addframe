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
		$result = $this->parseResult( $apiresult );

		if ($result == 'NeedToken') {
			$post['lgtoken'] = $apiresult['login']['token'];
			$apiresult = $this->api->doLogin(null,$post);
			$result = $this->parseResult( $apiresult );
		}

		if ($result === true) {
			print "Logged in\n";
			return $result;
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

		$apiresult = $this->api->doEdit($post);
		$result = $this->parseResult( $apiresult );

		print "Edit Returned: $result\n";
		return $result;
	}

	/**
	 * @param $results
	 * @return bool
	 * @todo create an api result class and parser
	 */
	function parseResult($results){
		foreach($results as $result)
		{
			if(isset($result['result'])){ $result = $result['result']; }
			else if(isset($result['code'])){ $result = $result['code']; }
			if($result == "Success"){
				return true;
			}
			else{
				return $result;
			}
		}
		return false;
	}
	
}