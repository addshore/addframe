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

		if ($post==null){
			$query = "?".http_build_query($query);
			$returned = $this->http->get($this->url.$query);
		} else {
			$query = "?".http_build_query($query);
			$returned = $this->http->post($this->url.$query,$post);
		}
		return new mediawikiapiresult(unserialize($returned));
	}

	/**
	 * This function returns and edit token from the api
	 * @return string Edit token.
	 **/
	function getEditToken () {
		if( isset( $this->token ) ){
			return $this->token;
		}
		$apiresult = $this->doRequest( array('action' => 'query', 'prop' => 'info','intoken' => 'edit', 'titles' => 'Main Page') );
		return $apiresult->value['query']['pages']['1']['edittoken'];
	}

	/**
	 * This function resets the edit token in case we need to get a new one
	 * //@todo catch token errors and call this to reset the token
	 */
	function resetEditToken () {
		unset( $this->token );
		return $this->getEditToken();
	}


	/*
	* Below this line we start to have module specific methods
	 * @todo it would be great to be able to include each module only if it is actually use onwiki (i.e. wikibase)
	**/

	function doLogin ($query,$post=null){
		$parameters['action'] = 'login';
		return $this->doRequest($parameters,$post);
	}

	function doEdit ($parameters){
		$parameters['action'] = 'edit';
		$parameters['token'] = $this->getEditToken();
		return $this->doRequest(null, $parameters );
	}

	function doPropRevsions($parameters){
		$parameters['action'] = 'query';
		$parameters['prop'] = 'revisions';
		$parameters['rvprop'] = 'timestamp|content';
		return $this->doRequest($parameters);
	}

	function doPropCategories($parameters){
		$parameters['action'] = 'query';
		$parameters['prop'] = 'categories';
		$parameters['clprop'] = 'hidden';
		$parameters['cllimit'] = '500';
		return $this->doRequest($parameters);
	}

	function doListAllusers($parameters){
		$parameters['action'] = 'query';
		$parameters['list'] = 'allusers';
		return $this->doRequest($parameters);
	}

	function doWbGetEntities ($parameters){
		$parameters['action'] = 'wbgetentities';
		return $this->doRequest($parameters );
	}

	function doWbEditEntity ($parameters){
		$parameters['action'] = 'wbeditentity';
		$parameters['token'] = $this->getEditToken();
		return $this->doRequest(null, $parameters );
	}

}