<?php

class mediawikiApi {
	private $http;
	private $token;
	private $ecTimestamp;
	public $url;

	function __construct ($url) {
		$this->http = new http;
		$this->token = null;
		$this->url = $url;
		$this->ecTimestamp = null;
	}

	/*
	* Performs a request to the api given the query and post data
	* @param $query Array of query data
	* @param $post Array of post data
	* @return Array of the returning data
	**/
	function doRequest ($query,$post=null){
		$query['format'] = 'php';
		$query = "?".http_build_query($query);
		if ($post==null)
			$ret = $this->http->get($this->url.$query);
		else
			$ret = $this->http->post($this->url.$query,$post);
		return unserialize($ret);
	}

	function doAction ($type,$post=null){
		$query['action'] = $type;
		return $this->doRequest ($query,$post);
	}

	function doLogin ($query,$post=null){return $this->doAction ('login',$post);}
	function doLogout () {return $this->doAction ('logout',null);}

	function doQuery ($parameters){return  $this->doAction ( 'query', $parameters);}

	function doEdit ($parameters){return  $this->doAction ( 'edit', array_merge($parameters,array('token' => $this->getedittoken() ) ) ) ;}

	/**
	 * This function returns and edit token from the api
	 * @return string Edit token.
	 **/
	function getedittoken () {
		$x = $this->doQuery( array('prop' => 'info','intoken' => 'edit', 'titles' => 'Main Page') );
		foreach ($x['query']['pages'] as $ret) {
			$token = $ret['edittoken'];
			return $ret['edittoken'];
		}
		return "";
	}

}