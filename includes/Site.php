<?php

/**
 * This class is designed to represent a mediawiki installation
 * @author Addshore
 **/

class Mediawiki {
	public $handel;
	public $url;
	private $http;
	private $token;
	private $loggedIn;
	/**
	 * @var UserLogin
	 */
	public $userlogin;

	/**
	 * @param $handel string handel used through script for this site
	 * @param $url string URL of the api
	 */
	function __construct ($handel, $url) {
		$this->handel = $handel;
		$this->url = $url;
		$this->http = new Http();
		$this->loggedIn = false;
	}

	function getPage ($page) {
		return new Page($this->handel,$page);
	}

	function getUser ($username) {
		return new User($this->handel,$username);
	}

	function getEntity ($id) {
		return new WikibaseEntity($this->handel,$id);
	}

	function setLogin($userLogin){
		$this->userlogin = $userLogin;
	}

	function newLogin($username, $password, $doLogin = false) {
		$this->setLogin( new UserLogin($this->handel,$username,$password) );
		if($doLogin === true){
			$this->doLogin();
		}
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
		return unserialize($returned);
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

	function getSitematrix () {
		//@todo catch if sitematrix isnt recognised by the api
		$siteArray = array();
		$returned = $this->doRequest( array('action' => 'sitematrix', 'smlangprop' => 'site') );
		foreach($returned['sitematrix'] as $langmatrix){
			foreach($langmatrix['site'] as $site){
				$siteArray[$site['dbname']] = $site;
			}

		}
		return $siteArray;
	}

	/**
	 * Logs in to the UserLogin associated with the site if not already logged in
	 * @return bool
	 * @throws Exception
	 */
	function doLogin () {
		if($this->loggedIn == true){
			return $this->loggedIn;
		}
		$post['action'] = 'login';
		$post['lgname'] = $this->userlogin->username;
		$post['lgpassword'] = $this->userlogin->getPassword();

		$result = $this->doRequest(null,$post);

		if ($result->statusCode == 'NeedToken') {
			$post['lgtoken'] = $result->getInside()['token'];
			$result = $this->doRequest(null,$post);
		}

		if ($result->statusCode == "Success") {
			$this->loggedIn = true;
			return $this->loggedIn;
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
		$parameters['action'] = 'edit';
		$parameters['title'] = $title;
		$parameters['text'] = $text;
		if( isset($summary) ) { $parameters['summary'] = $summary; }
		if( $minor == true ) { $parameters['minor'] = '1'; }
		$parameters['token'] = $this->getEditToken();
		return $this->doRequest( null, $parameters );
	}

	function doPropRevsions( $parameters ){
		$parameters['action'] = 'query';
		$parameters['prop'] = 'revisions';
		$parameters['rvprop'] = 'timestamp|content';
		return $this->doRequest( $parameters );
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