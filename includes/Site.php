<?php

namespace Addframe;

/**
 * This class is designed to represent a mediawiki installation
 * @author Addshore
 **/

class Site {
	/**
	 * @var Family family the site is associated to
	 */
	public $family;
	/**
	 * @var Site|bool wikibase the site is associated to
	 */
	private $wikibase;
	/**
	 * @var string id of the site
	 */
	private $id;
	/**
	 * @var string type of site eg.(wiki|wikivoyage)
	 */
	private $type;
	/**
	 * @var string url of the site
	 */
	public $url;
	/**
	 * @var string url of the api
	 */
	private $api;
	/**
	 * @var string language eg. en
	 */
	private $language;
	/**
	 * @var string url of the wikibase for the site
	 */
	public $wikibaseurl;
	/**
	 * @var Http class
	 */
	private $http;
	/**
	 * @var string cache of the token we are using
	 */
	private $token;
	/**
	 * @var Array
	 */
	private $namespaces;
	/**
	 * @var UserLogin
	 */
	private $userlogin;

	public function setLogin( $userLogin ) {
		$this->userlogin = $userLogin;
	}

	/**
	 * @param $url string URL of the api
	 * @param null $family Family
	 */
	public function __construct( $url, $family = null ) {
		$this->url = $url;
		$this->http = new Http();

		if ( isset( $family ) ) {
			$this->family = $family;
		}
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
			return $this->namespaces[$id][0];
		}
		if ( $id == '0' ) {
			return '';
		}
		throw new \Exception( "Could not return a namespace for id $id in " . $this->url );
	}

	//find the nsid id from the title
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

	public function newLogin( $username, $password, $doLogin = false ) {
		$this->setLogin( new UserLogin( $username, $password ) );
		if ( $doLogin === true ) {
			$this->requestLogin();
		}
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
				$this->requestLogin();
			}
			$query = "?" . http_build_query( $query );
			$returned = $this->http->post( $apiurl . $query, $post );
		}
		return unserialize( $returned );
	}

	/**
	 * Gets the api url from the main entry point
	 */
	public function requestApiUrl() {
		$pageData = $this->http->get( $this->url );
		//@todo should die if cant contact site!
		preg_match( '/\<link rel=\"EditURI.*?$/im', $pageData, $pageData );
		if ( ! isset( $pageData[0] ) ) {
			throw new \Exception( "Undefined offset when getting EditURL (api url)" );
		}
		preg_match( '/href=\"([^\"]+)\"/i', $pageData[0], $pageData );
		if ( ! isset( $pageData[1] ) ) {
			throw new \Exception( "Undefined offset when getting EditURL (api url)" );
		}
		$parsedApiUrl = parse_url( $pageData[1] );
		$this->api = $parsedApiUrl['host'] . $parsedApiUrl['path'];
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
		return $apiresult['query']['pages']['-1']['edittoken'];
	}

	public function requestSitematrix() {
		//@todo catch if sitematrix isnt recognised by the api
		$siteArray = array();
		$returned = $this->doRequest( array( 'action' => 'sitematrix', 'smlangprop' => 'site' ) );
		if ( $returned == null ) {
			die( "Sitematrix failed... Maybe you are offline." );
		}
		foreach ( $returned['sitematrix'] as $key => $langmatrix ) {
			if ( $key == 'count' ) {
				continue;
			} //skip the count of sites..
			if ( $key == 'specials' ) {
				foreach ( $langmatrix as $site ) {
					$siteArray[$site['dbname']] = $site;
				}
			} else {
				//this is the default
				foreach ( $langmatrix['site'] as $site ) {
					$siteArray[$site['dbname']] = $site;
				}
			}
		}
		return $siteArray;
	}

	/**
	 * Gets and returns array of namespaces for the site and aliases
	 *
	 * @return array of namespaces
	 */
	//@todo specify a single nsid to return
	public function requestNamespaces() {
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
			$this->wikibase = $this->family->getSite( $parsedApiUrl['host'] );
		}
	}

	/**
	 * Logs in to the UserLogin associated with the site if not already logged in
	 * @return bool
	 * @throws \Exception
	 */
	public function requestLogin() {
		if ( !isset( $this->token ) ) {
			echo "Loging in to " . $this->url . "\n";
			$post['action'] = 'login';
			$post['lgname'] = $this->userlogin->username;
			$post['lgpassword'] = $this->userlogin->getPassword();

			$result = $this->doRequest( null, $post );

			if ( $result['login']['result'] == 'NeedToken' ) {
				$post['lgtoken'] = $result['login']['token'];
				$result = $this->doRequest( null, $post );
			}

			if ( $result['login']['result'] == "Success" ) {
				return true;
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

}