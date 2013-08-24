<?php

namespace Addframe\Mediawiki;
use Addframe\Http;

/**
 * Class Site - Represents a mediawiki site
 * @package Addframe\Mediawiki
 */

class Site {

	/** @var string  */
	protected $url = null;
	/** @var Http  */
	protected $http;

	/** @var Api  */
	public $api = null;

	public function __construct( $http = null ) {
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

	public function getApi(){
		if( is_null( $this->api ) ){
			$this->getApiFromHomePage();
		}
		return $this->api;
	}

	public static function newFromUrl( $url ){
		$site = new Site( );
		$site->setUrl( $url );
		return $site;
	}

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

	public function getToken( $type = 'edit' ){
		$apiResult = $this->getApi()->doRequest( new TokensRequest( $type ) );
		return $apiResult['tokens'][$type.'token'];
	}

}