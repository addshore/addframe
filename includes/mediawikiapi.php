<?php

/**
 * This class is designed to represet a medawiki api
 * @author Addshore
 **/

class MediawikiAPI {
	private $http;
	private $token;
	public $url;

	/**
	 * @param $url string Location of the API
	 */
	function __construct ($url) {
		$this->http = new http;
		$this->token = null;
		$this->url = $url;
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
			$returned = $this->http->get($this->url.$query);
		else
			$returned = $this->http->post($this->url.$query,$post);
		return new mediawikiapiresult(unserialize($returned));
	}

	function doAction ($type,$post=null){
		$query['action'] = $type;
		return $this->doRequest ($query,$post);
	}

	function doLogin ($query,$post=null){
		return $this->doAction ('login',$post);
	}

	function doLogout () {
		return $this->doAction ('logout',null);
	}

	function doQuery ($parameters){
		return  $this->doAction ( 'query', $parameters);
	}

	function doEdit ($parameters){
		return $this->doAction ( 'edit', $this->mergeToken($parameters) );
	}

	function doPropRevsions($parameters){
		$parameters['prop'] = 'revisions';
		$parameters['rvprop'] = 'timestamp|content';
		return $this->doQuery($parameters);
	}

	function doPropCategories($parameters){
		$parameters['prop'] = 'categories';
		$parameters['clprop'] = 'hidden';
		$parameters['cllimit'] = '500';
		return $this->doQuery($parameters);
	}

	function doListAllusers($parameters){
		$parameters['list'] = 'allusers';
		return $this->doQuery($parameters);
	}

	/**
	 * Merges the an edit token an array of parameters
	 * @param $array
	 * @return array
	 */
	function mergeToken($array){
		return array_merge( $array,array('token' => $this->getEditToken() ) );
	}

	/**
	 * This function returns and edit token from the api
	 * @return string Edit token.
	 **/
	function getEditToken () {
		if( $this->token != null ){
			return $this->token;
		}
		$apiresult = $this->doQuery( array('prop' => 'info','intoken' => 'edit', 'titles' => 'Main Page') );
		return $apiresult->value['query']['pages']['1']['edittoken'];
	}

	/**
	 * This function resets the edit token incase we need to get a new one
	 */
	function resetEditToken () {
		$this->token = null;
	}

}