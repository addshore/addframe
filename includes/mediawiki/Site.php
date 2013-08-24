<?php

namespace Addframe\Mediawiki;

use Addframe\Http;

/**
 * Class Site - Represents a mediawiki site
 * @package Addframe\Mediawiki
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

	public function setUrl( $url ){
		$this->url = $url;
	}

	public function getUrl(){
		return $this->url;
	}

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
	 * Gets a list of all availible tokens
	 * @return array of all available tokens
	 */
	public function getTokenList(){
		$availibeTokens = 'block|delete|edit|email|import|move|options|patrol|protect|unblock|watch';
		$apiResult = $this->getApi()->doRequest( new TokensRequest( $availibeTokens ) );
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
		$apiResult = $this->getApi()->doRequest( new TokensRequest( $type ) );
		if( array_key_exists( 'tokens', $apiResult ) ){
			return $apiResult['tokens'][$type.'token'];
		}
		return null;
	}

}