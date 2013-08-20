<?php
/**
 * Created by JetBrains PhpStorm.
 * User: adam
 * Date: 20/08/13
 * Time: 17:11
 * To change this template use File | Settings | File Templates.
 */

namespace Addframe\Mediawiki;


class Api {

	/** @var string url of the api */
	public $url;
	/** @var Site site */
	public $site;
	protected $isLoggedIn = false;

	function __construct( $site ) {
		$this->site = $site;
	}

	protected function getUrl(){
		if( is_null( $this->url ) ){
			$this->url = $this->site->getApiUrl();
		}
		return $this->url;
	}

	/*
	* Performs a request to the api given the query and post data
	* @param $query Array of query data
	* @param $post Array of post data
	* @return Array of the returning data
	**/
	public function doRequest( $query, $post = null ) {
		// Normalize some stuff
		if( is_array( $query ) ){
			foreach( $query as $param => $value ) {
				if ( is_array( $value ) ) {
					$query[$param] = implode( '|', $value );
				}
			}
		}

		$query['format'] = 'php';

		if ( $post == null ) {
			$query = "?" . http_build_query( $query );
			$returned = $this->site->http->get( $this->getUrl() . $query );
		} else {
			if ( $post['action'] != 'login' ) {
				$this->requestEditToken();
			}
			$query = "?" . http_build_query( $query );
			$returned = $this->site->http->post( $this->getUrl() . $query, $post );
		}
		return unserialize( $returned );
	}

	/**
	 * Logs in to the UserLogin associated with the site if not already logged in
	 * @param null $token
	 * @return bool
	 */
	public function requestLogin( $token = null ) {
		$post['action'] = 'login';
		$post['lgname'] = $this->site->userlogin->username;
		$post['lgpassword'] = $this->site->userlogin->getPassword();
		if( !is_null( $token ) ){
			$post['lgtoken'] = $token;
		}

		return $this->doRequest( null, $post );
	}

	/**
	 * This function returns and edit token from the api
	 * @throws \Exception
	 * @return string Edit token.
	 */
	public function requestEditToken() {
		$this->site->login();
		$apiresult = $this->doRequest( array( 'action' => 'query', 'prop' => 'info', 'intoken' => 'edit', 'titles' => 'Main Page' ) );
		foreach($apiresult['query']['pages'] as $value){
			return $value['edittoken'];
		}
		throw new \Exception("Failed to get token");
	}

	/**
	 * Gets and returns array of namespaces for the site and aliases
	 * @return Array
	 */
	public function requestNamespaces() {
		return $this->doRequest( array( 'action' => 'query', 'meta' => 'siteinfo', 'siprop' => 'namespaces|namespacealiases' ) );
	}

	public function requestSiteinfo() {
		$q['action'] = 'query';
		$q['meta'] = 'siteinfo';
		return $this->doRequest( $q );
	}

	public function requestWikibaseinfo() {
		$q['action'] = 'query';
		$q['meta'] = 'wikibase';
		return $this->doRequest( $q );
	}

	/**
	 * @param $title string Title to be edited
	 * @param $text string Text to be placed
	 * @param null $summary Edit Summary
	 * @param bool $minor Do we want to mark the edit as minor?
	 * @return string
	 */
	public function requestEdit( $title, $text, $summary = null, $minor = false ) {
		$parameters['action'] = 'edit';
		$parameters['title'] = $title;
		$parameters['text'] = $text;
		$parameters['bot'] = '';
		$parameters['maxlag'] = '5';
		if ( isset( $summary ) ) {
			$parameters['summary'] = $summary;
		}
		if ( $minor == true ) {
			$parameters['minor'] = '1';
		}
		$parameters['token'] = $this->requestEditToken();
		return $this->doRequest( null, $parameters );
	}

	public function requestPropRevsions( $parameters ) {
		$parameters['action'] = 'query';
		$parameters['prop'] = 'revisions';
		$parameters['rvprop'] = 'timestamp|content';
		return $this->doRequest( $parameters );
	}

	public function requestPropCategories( $parameters ) {
		$parameters['action'] = 'query';
		$parameters['prop'] = 'categories';
		$parameters['clprop'] = 'hidden';
		$parameters['cllimit'] = '500';
		return $this->doRequest( $parameters );
	}

	public function requestListAllusers( $parameters ) {
		$parameters['action'] = 'query';
		$parameters['list'] = 'allusers';
		return $this->doRequest( $parameters );
	}

	public function requestListUsers( $parameters ) {
		$parameters['action'] = 'query';
		$parameters['list'] = 'users';
		return $this->doRequest( $parameters );
	}

	public function requestWbGetEntities( $parameters ) {
		$parameters['action'] = 'wbgetentities';
		return $this->doRequest( $parameters );
	}

	public function requestWbEditEntity( $parameters ) {
		$parameters['action'] = 'wbeditentity';
		$parameters['token'] = $this->requestEditToken();
		return $this->doRequest( null, $parameters );
	}

	public function requestPropCoordinates( $parameters ) {
		$parameters['coprop'] = 'dim|globe';
		$parameters['action'] = 'query';
		$parameters['prop'] = 'coordinates';
		return $this->doRequest( null, $parameters );
	}

	public function requestWbGetClaims( $parameters ) {
		$parameters['action'] = 'wbgetclaims';
		return $this->doRequest( null, $parameters );
	}

	public function requestWbCreateClaim( $parameters ) {
		$parameters['action'] = 'wbcreateclaim';
		$parameters['token'] = $this->requestEditToken();
		$parameters['bot'] = '';
		return $this->doRequest( null, $parameters );
	}

	public function requestWbSetReference( $parameters ) {
		$parameters['action'] = 'wbsetreference';
		$parameters['token'] = $this->requestEditToken();
		$parameters['bot'] = '';
		return $this->doRequest( null, $parameters );
	}

	public function requestListCategoryMembers( $parameters ){
		$parameters['action'] = 'query';
		$parameters['list'] = 'categorymembers';
		return $this->doRequest( null, $parameters );
	}

}