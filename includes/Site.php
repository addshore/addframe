<?php

namespace Addframe;

/**
 * This class is designed to represent a mediawiki installation
 * @author Addshore
 **/

class Site {
	/** @var Family family the site is associated to */
	public $family;

	/** @var Site|bool wikibase the site is associated to */
	private $wikibase;
	/** @var string id of the site */
	private $id;
	/** @var string type of site eg.(wiki|wikivoyage) */
	private $type;
	/** @var string url of the site */
	public $url;
	/** @var string url of the api */
	private $api;
	/** @var string language eg. en */
	private $language;
	/** @var string iwPrefix eg. en or simple */
	private $iwPrefix;
	/** @var Http class */
	private $http;
	/** @var string cache of the token we are using */
	private $token;
	private $isLoggedIn = false;
	/** @var Array */
	private $namespaces;
	/** @var UserLogin */
	private $userlogin;

	public function setLogin( $userLogin ) {
		$this->userlogin = $userLogin;
	}

	/**
	 * @param $url string URL of the api
	 * @param Http|null $http
	 * @param Family|null $family Family
	 * @throws \Exception
	 */
	public function __construct( $url, Http $http = null , Family $family = null ) {
		if( empty ( $url ) ){
			throw new \Exception('Can not construct a site without a url');
		}
		$this->url = $url;

		if( ! $http instanceof Http ){
			$this->http = new Http();
		} else {
			$this->http = $http;
		}

		if ( isset( $family ) ) {
			$siteDetails = $family->getSiteDetailsFromSiteIndex('url', $url);
			if( $siteDetails !== null ){
				$this->language = $siteDetails['lang'];
				$this->type = $siteDetails['code'];
			}
			$this->family = $family;
		}
	}

	public function getUserLogin(){
		return $this->userlogin;
	}

	public function getApiUrl(){
		if ( $this->api == null ){
			$this->requestApiUrl();
		}
		return $this->api;
	}

	public function getType(){
		if ( $this->type == null ){
			$this->requestSiteinfo();
		}
		return $this->type;
	}

	public function getId(){
		if ( $this->id == null ){
			$this->requestSiteinfo();
		}
		return $this->id;
	}

	public function getLanguage(){
		if ( $this->language == null ){
			$this->requestSiteinfo();
		}
		return $this->language;
	}

	public function getIwPrefix(){
		if( $this->url == 'simple.wikipedia.org' ){
			return 'simple';
		}
		return $this->getLanguage();
	}

	public function getWikibase(){
		if ( $this->wikibase == null ){
			$this->requestWikibaseinfo();
		}
		return $this->wikibase;
	}

	public function getNamespaceFromId( $id ) {
		if ( ! isset( $this->namespaces ) ) {
			$this->requestNamespaces();
		}
		if ( isset( $this->namespaces[$id] ) ) {
			if( $this->namespaces[$id][0] == ''){
				return $this->namespaces[$id][0];
			} else {
				return $this->namespaces[$id][0].':';
			}
		}
		throw new \Exception( "Could not return a namespace for id $id in " . $this->url );
	}

	public function getNamespaceIdFromTitle( $title ) {
		$explosion = explode( ':', $title );
		if ( isset( $explosion[0] ) ) {
			$this->requestNamespaces();
			foreach ( $this->namespaces as $nsid => $namespaceArray ) {
				foreach ( $namespaceArray as $namespace ) {
					if ( $explosion[0] == $namespace ) {
						return $nsid;
					}
				}

			}
		}
		return '0';
	}

	public function getPageTextFromPageTitle( $title ) {
		echo "Loading page " . $this->url . " " . $title . "\n";
		$param['titles'] = $title;

		$result = $this->requestPropRevsions( $param );

		foreach ( $result['query']['pages'] as $x ) {
			$this->nsid=  $x['ns'];
			if ( ! isset( $x['missing'] ) ) {
				if( isset( $x['revisions']['0']['*'] ) ){
					return $x['revisions']['0']['*'];
				} else {
					throw new \Exception( 'Request to get page text did not have expected key' );
				}
			} else {
				return '';
			}
		}
		return null;
	}

	/**
	 * This function resets the edit token in case we need to get a new one
	 * //@todo catch token errors and call this to reset the token
	 */
	public function resetEditToken() {
		unset( $this->token );
		return $this->requestEditToken();
	}

	/**
	 * @param $title string
	 * @return Page
	 */
	public function newPageFromTitle( $title ) {
		return new Page( $this, $title );
	}

	/**
	 * @param $username string
	 * @return User
	 */
	public function newUserFromUsername( $username ) {
		return new User( $this, $username );
	}

	/**
	 * @param $id string
	 * @return Entity
	 */
	public function newEntityFromEntityId( $id ) {
		return new Entity( $this, $id );
	}

	public function newLogin( $username, $password ) {
		$this->userlogin = new UserLogin( $username, $password );
	}

	/*
	* Performs a request to the api given the query and post data
	* @param $query Array of query data
	* @param $post Array of post data
	* @return Array of the returning data
	**/
	public function doRequest( $query, $post = null ) {
		$apiurl = $this->getApiUrl();
		$query['format'] = 'php';

		if ( $post == null ) {
			$query = "?" . http_build_query( $query );
			$returned = $this->http->get( $apiurl . $query );
		} else {
			if ( $post['action'] != 'login' ) {
				$this->requestEditToken();
			}
			$query = "?" . http_build_query( $query );
			$returned = $this->http->post( $apiurl . $query, $post );
		}
		return unserialize( $returned );
	}

	/**
	 * Gets the api url from the main entry point
	 * Hacky html screen scrape..
	 * @todo this should probably be renamed
	 */
	public function requestApiUrl() {
		$pageData = $this->http->get( $this->url );
		preg_match( '/\<link rel=\"EditURI.*?$/im', $pageData, $apiData );
		if ( ! isset( $apiData[0] ) ) {
			throw new \Exception( "Undefined offset when getting EditURL (api url)" );
		}
		preg_match( '/href=\"([^\"]+)\"/i', $apiData[0], $apiData );
		if ( ! isset( $apiData[1] ) ) {
			throw new \Exception( "Undefined offset when getting EditURL (api url)" );
		}
		$parsedApiUrl = parse_url( $apiData[1] );
		
		//Note: The below is back compatability check for the parse_url function
		if( array_key_exists('host', $parsedApiUrl) ){
			$this->api = $parsedApiUrl['host'] . $parsedApiUrl['path'];
		} else {
			//pre 5.4.7
			$this->api = trim($parsedApiUrl['path'] ,'/');
		}

		//Now hackily try and parse the lang
		preg_match( '/\<html lang=\"([^\"]+)\"/im', $pageData, $langData );
		if( ! empty($langData[1]) ){
			$this->language = $langData[1];
		}
	}

	/**
	 * This function returns and edit token from the api
	 * @return string Edit token.
	 **/
	public function requestEditToken() {
		if ( isset( $this->token ) ) {
			return $this->token;
		}
		$this->requestLogin();
		$apiresult = $this->doRequest( array( 'action' => 'query', 'prop' => 'info', 'intoken' => 'edit', 'titles' => 'Main Page' ) );
		foreach($apiresult['query']['pages'] as $value){
			$this->token = $value['edittoken'];
			return $this->token;
		}
	}

	public function requestSitematrix() {
		$returned = $this->doRequest( array( 'action' => 'sitematrix') );

		if ( empty ( $returned ) ) {
			//@todo also catch if the result is returned but with an error code (not recognised action)
			throw new \Exception( "Sitematrix empty... Maybe you are offline." );
		}

		//add language to the site details
		foreach( $returned['sitematrix'] as $groupKey => $group){
			if( $groupKey == 'count' || $groupKey == 'specials' ) { continue; }
			foreach( $group['site'] as $siteKey => $site ){
				$returned['sitematrix'][$groupKey]['site'][$siteKey]['lang'] = $group['code'];
			}
		}

		return $returned['sitematrix'];
	}

	/**
	 * Gets and returns array of namespaces for the site and aliases
	 * @param integer $nsid Array of namespaces to return
	 * @return Array
	 */
	public function requestNamespaces( $nsid = null ) {
		if ( ! isset( $this->namespaces ) ) {
			$returned = $this->doRequest( array( 'action' => 'query', 'meta' => 'siteinfo', 'siprop' => 'namespaces|namespacealiases' ) );
			$this->namespaces[0] = Array( '' );
			foreach ( $returned['query']['namespaces'] as $key => $nsArray ) {
				if ( $nsArray['id'] != '0' ) {
					$this->namespaces[$key][] = $nsArray['*'];
					$this->namespaces[$key][] = $nsArray['canonical'];
				}
			}
			foreach ( $returned['query']['namespacealiases'] as $nsArray ) {
				$this->namespaces[$nsArray['id']][] = $nsArray['*'];
			}
		}

		if( $nsid !== null ){
			if( isset( $this->namespaces[$nsid] ) ){
				return $this->namespaces[$nsid];
			}
		}
		return $this->namespaces;
	}

	public function requestSiteinfo() {
		$q['action'] = 'query';
		$q['meta'] = 'siteinfo';
		$result = $this->doRequest( $q );
		$this->id = $result['query']['general']['wikiid'];
		$this->language = $result['query']['general']['lang'];
		$this->type = preg_replace( '/^' . $this->getLanguage() . '/i', '', $this->getId() );
	}

	public function requestWikibaseinfo() {
		$q['action'] = 'query';
		$q['meta'] = 'wikibase';
		$result = $this->doRequest( $q );
		if ( isset( $result['query']['wikibase']['repo']['url']['base'] ) ) {
			$parsedApiUrl = parse_url( $result['query']['wikibase']['repo']['url']['base'] );
			
			//Note: The below is back compatability check for the parse_url function
			if( array_key_exists('host', $parsedApiUrl) ){
				$this->wikibase = $this->family->getSite( $parsedApiUrl['host'] );
			} else {
				//pre 5.4.7
				$this->wikibase = $this->family->getSite( trim($parsedApiUrl['path'] ,'/') );
			}
		}
	}

	/**
	 * Logs in to the UserLogin associated with the site if not already logged in
	 * @return bool
	 * @throws \Exception
	 */
	public function requestLogin() {
		if ( $this->isLoggedIn == false ) {
			$post['action'] = 'login';
			$post['lgname'] = $this->userlogin->username;
			$post['lgpassword'] = $this->userlogin->getPassword();

			$result = $this->doRequest( null, $post );

			if ( $result['login']['result'] == 'NeedToken' ) {
				$post['lgtoken'] = $result['login']['token'];
				$result = $this->doRequest( null, $post );
			}

			if ( $result['login']['result'] == "Success" ) {
				echo "Logged in to " . $this->url . "\n";
				$this->isLoggedIn = true;
				return $this->isLoggedIn;
			} else if ( $result['login']['result'] == "Throttled" ) {
				echo "Throttled! Waiting for " . $result['login']['wait'] . "\n";
				sleep( $result['login']['wait'] );
				return $this->requestLogin();
			} else {
				throw new \Exception( 'Failed login, with result ' . $result['login']['result'] );
			}
		}
		return null;
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

}