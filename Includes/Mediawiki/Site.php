<?php

namespace Addframe\Mediawiki;
use Addframe\Addframe;
use Addframe\Http;
use Addframe\Mediawiki\Wikibase\Entity;

/**
 * This class is designed to represent a mediawiki installation
 * @author Addshore
 * @since 0.0.1
 **/

class Site {
	/** @var Family family the site is associated to */
	public $family;

	/** @var Site|bool wikibase the site is associated to */
	protected $wikibase;
	/** @var string id of the site */
	protected $id;
	/** @var string type of site eg.(wiki|wikivoyage) */
	protected $type;
	/** @var string url of the site */
	public $url;
	/** @var Api api for the site */
	public $api; //todo should be made protected once everything is factored into the right places
	/** @var string language eg. en */
	protected $language;
	/** @var string iwPrefix eg. en or simple */
	protected $iwPrefix;
	/** @var Http class */
	public $http;
	/** @var string cache of the token we are using */
	protected $token;
	protected $isLoggedIn = false;
	/** @var Array */
	protected $namespaces;
	/** @var UserLogin */
	public $userlogin; //todo create getter and setter and make protected

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

		$this->api = new Api( $this );
	}

	public function hasToken(){
		return !is_null( $this->token );
	}

	public function setLogin( $userLogin ) {
		$this->userlogin = $userLogin;
	}

	public function getUserLogin(){
		return $this->userlogin;
	}

	public function getType(){
		if ( $this->type == null ){
			$this->getSiteinfo();
		}
		return $this->type;
	}

	public function getId(){
		if ( $this->id == null ){
			$this->getSiteinfo();
		}
		return $this->id;
	}

	public function getLanguage(){
		if ( $this->language == null ){
			$this->getSiteinfo();
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
			$this->getWikibaseinfo();
		}
		return $this->wikibase;
	}

	public function getNamespaceFromId( $id ) {
		if ( ! isset( $this->namespaces ) ) {
			$this->getNamespaces();
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
			$this->getNamespaces();
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

	public function getPageTextFromPageTitle( $title, $expanded = false) {
		$param['titles'] = $title;
		if( $expanded ){
			$param['rvexpandtemplates'] = '';
		}

		$result = $this->api->requestPropRevsions( $param );

		foreach ( $result['query']['pages'] as $x ) {
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
	 * @param $title string
	 * @return Page
	 */
	public function newPageFromTitle( $title ) {
		return new Page( $this, $title );
	}

	public function getPageInfo( $title ) {
		$params = array(
			'action' => 'query',
			'prop' => 'info',
			'titles' => $title,
			'inprop' => array( 'url' ),
		);
		$data = $this->api->doRequest( $params );
		$data = array_values( $data['query']['pages'] );
		$data = $data[0];
		return $data;
	}

	//@todo newPageFromPageId()

	public function newCategoryFromTitle( $title ) {
		return new Category( $this, $title );
	}

	/**
	 * @param $username string
	 * @return User
	 */
	public function newUserFromUsername( $username ) {
		return new User( $this, $username );
	}

	public function newLogin( $username, $password ) {
		$this->userlogin = new UserLogin( $username, $password );
	}

	/**
	 * Gets the api url from the main entry point
	 * Hacky html screen scrape..
	 */
	public function getApiUrl() {
		$pageData = $this->http->get( $this->url );
		preg_match( '/\<link rel=\"EditURI.*?$/im', $pageData, $apiData );
		if ( ! isset( $apiData[0] ) ) {
			throw new \Exception( "Undefined offset when getting EditURL (api url) stage1" );
		}
		preg_match( '/href=\"([^\"]+)\"/i', $apiData[0], $apiData );
		if ( ! isset( $apiData[1] ) ) {
			throw new \Exception( "Undefined offset when getting EditURL (api url) stage2" );
		}
		$parsedApiUrl = parse_url( $apiData[1] );
		
		//Note: The below is back compatability check for the parse_url function
		if( array_key_exists('host', $parsedApiUrl) ){
			return $parsedApiUrl['host'] . $parsedApiUrl['path'];
		} else {
			//pre 5.4.7
			return trim($parsedApiUrl['path'] ,'/');
		}
	}

	public function getSiteMatrix() {
		$returned = $this->api->doRequest( array( 'action' => 'sitematrix') );

		if ( empty ( $returned ) ) {
			//@todo also catch if the result is returned but with an error code (not recognised action)
			throw new \Exception( "Sitematrix empty... Maybe you are offline." );
		} else if( array_key_exists( 'error', $returned ) && $returned['error']['code'] == "unknown_action" ) {
			throw new \Exception ( "No Sitematrix availible.. ".$returned['error']['code'] );
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
	 * @todo this needs to be cached
	 */
	public function getNamespaces( $nsid = null ) {
		if ( ! isset( $this->namespaces ) ) {
			$returned = $this->api->requestNamespaces();
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

	public function getSiteinfo() {
		$result = $this->api->requestSiteinfo();
		$this->id = $result['query']['general']['wikiid'];
		$this->language = $result['query']['general']['lang'];
		$this->type = preg_replace( '/^' . $this->getLanguage() . '/i', '', $this->getId() );
	}

	public function getWikibaseinfo() {
		$result = $this->api->requestWikibaseinfo();
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
	public function login() {
		if ( $this->isLoggedIn == false ) {
			$result = $this->api->requestLogin();

			if ( $result['login']['result'] == 'NeedToken' ) {
				$result = $this->api->requestLogin( $result['login']['token'] );
			}

			if ( $result['login']['result'] == "Success" ) {
				Addframe::log( "Logged in to " . $this->url . "\n" );
				$this->isLoggedIn = true;
				return $this->isLoggedIn;
			} else if ( $result['login']['result'] == "Throttled" ) {
				Addframe::log( "Throttled! Waiting for " . $result['login']['wait'] . "\n" );
				sleep( $result['login']['wait'] );
				return $this->login();
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
	public function doEdit( $title, $text, $summary = null, $minor = false ) {
		$result = $this->api->requestEdit( $title, $text, $summary, $minor );
		if( array_key_exists( 'error', $result ) && $result['error']['code'] == 'maxlag' ){
			sleep(5);
			$result = $this->doEdit( $title, $text, $summary, $minor );
		}
		if( $this->getLanguage() == 'ja' && $this->getType() == 'wiki' ){
			sleep(60);
		}
		return $result;
	}
}