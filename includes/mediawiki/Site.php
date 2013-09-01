<?php

namespace Addframe\Mediawiki;

use Addframe\Http;
use Addframe\Mediawiki\Api;
use Addframe\Mediawiki\Api\LoginRequest;
use Addframe\Mediawiki\Api\TokensRequest;

/**
 * Class Site - Represents a Mediawiki site
 */

class Site {

	/**
	 * @var string the url to the root of the site
	 */
	protected $url = null;
	/**
	 * @var Http object to use for connecting to the site
	 */
	protected $http;
	/**
	 * @var Api object to use for connecting to the site api
	 */
	protected $api = null;

	/**
	 * This should generally not be used, use Site::new* instead
	 */
	/* protected */ public function __construct( $http = null ) {
		if( is_null( $http ) ){
			$this->http = Http::getDefaultInstance();
		} else {
			$this->http = $http;
		}

	}

	/**
	 * @param $url string url of the site
	 */
	public function setUrl( $url ){
		$url = trim( str_replace( array('http://','https://','//'), '', $url ), '/');
		$this->url = $url;
	}

	/**
	 * @return null|string current set url for the site
	 */
	public function getUrl(){
		return $this->url;
	}

	/**
	 * @param $api Api
	 */
	public function setApi( $api ){
		$this->api = $api;
	}

	/**
	 * Gets the Api object for the site
	 * If the Api is not yet defined we will try to get it from the home page
	 * @return Api|null
	 */
	public function getApi(){
		if( is_null( $this->api ) ){
			$this->getApiFromHomePage();
		}
		return $this->api;
	}

	/**
	 * Creates a new Site class using the given url
	 * @param $url string to create the class with
	 * @return Site
	 */
	public static function newFromUrl( $url ){
		$site = new Site( );
		$site->setUrl( $url );
		return $site;
	}

	/**
	 * Gets the location of the API using the EditURI link tag on a mediawiki homepage
	 * @return bool true if successful
	 */
	public function getApiFromHomePage() {
		if( !is_null( $this->url ) ){

			$homePage = New \DOMDocument();
			$homePage->loadHTML( $this->http->get( $this->url ) );

			foreach( $homePage->getElementsByTagName( 'link' ) as $element ){
				if( $element->attributes->getNamedItem('rel')->nodeValue == 'EditURI' ){
					$rsdUrl = $element->attributes->getNamedItem('href')->nodeValue;
					$apiUrl = str_replace( '?action=rsd', '', $rsdUrl );
					$this->api = Api::newFromUrl( $apiUrl );
					return true;
				}
			}

		}
		return false;
	}

	/**
	 * Gets a list of all available tokens
	 * @return array of all available tokens
	 */
	public function getTokenList(){
		$availibeTokens = 'block|delete|edit|email|import|move|options|patrol|protect|unblock|watch';
		$apiResult = $this->getApi()->doRequest( new TokensRequest( array( 'type' => $availibeTokens ) ) );
		if( array_key_exists( 'tokens', $apiResult ) ){
			return $apiResult['tokens'];
		}
		return array();
	}

	/**
	 * Get a single action token
	 * @param string $type type of token to get
	 * @return null|string the token of type requested
	 */
	public function getToken( $type = 'edit' ){
		$request = new TokensRequest( array( 'type' => $type ) );
		$apiResult = $this->getApi()->doRequest( $request );
		if( array_key_exists( 'tokens', $apiResult ) && array_key_exists( $type.'token', $apiResult['tokens'] )){
			return $apiResult['tokens'][$type.'token'];
		}
		return null;
	}

	/**
	 * Log in to the site
	 * @param $username string username to log in with
	 * @param $password string password to log in with
	 * @param null $token token to log in with (if none is set we will get one within)
	 * @return bool|null success of the login (null if unknown)
	 * @throws \Exception
	 */
	public function login( $username, $password, $token = null){
		$params = array( 'lgname' => $username, 'lgpassword' => $password );
		if( !is_null( $token ) ){
			$params['lgtoken'] = $token;
		}
		$request = new LoginRequest( $params );
		$result = $this->getApi()->doRequest( $request );
		if( array_key_exists( 'login', $result ) && array_key_exists( 'result', $result['login'] ) && $result['login']['result'] == 'NeedToken' ){
			return $this->login( $username, $password, $result['login']['token'] );
		} else if( array_key_exists( 'login', $result ) && array_key_exists( 'result', $result['login'] ) && $result['login']['result'] == 'Success' ){
			return true;
		} else if( array_key_exists( 'login', $result ) && array_key_exists( 'result', $result['login'] ) && $result['login']['result'] == 'WrongToken' ){
			return false;
		}
		//todo catch all other possible errors for logging in
		return null;
	}

	/**
	 * Log out of the site
	 * @returns bool always true
	 */
	public function logout(){
		$this->getApi()->doRequest( new Api\LogoutRequest() );
		return true;
	}

	/**
	 * @param $username string username
	 * @return User with the given username on this site
	 */
	public function getUser( $username ){
		$user = User::newFromUsername( $username, $this );
		return $user;
	}

}