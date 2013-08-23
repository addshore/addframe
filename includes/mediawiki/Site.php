<?php

namespace Addframe\Mediawiki;
use Addframe\Http;

/**
 * Class Site - Represents a mediawiki site
 * @package Addframe\Mediawiki
 */

class Site {

	protected $url = null;
	protected $apiUrl = null;
	/** @var Http  */
	protected $http;

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

	public function setApiUrl( $url ){
		$this->apiUrl = $url;
	}

	public function getApiUrl(){
		if( is_null( $this->apiUrl ) ){
			$this->getApiUrlFromHomePage();
		}
		return $this->apiUrl;
	}

	public static function newFromUrl( $url ){
		$site = new Site( );
		$site->setUrl( $url );
		return $site;
	}

	private function getApiUrlFromHomePage() {
		if( !is_null( $this->url ) ){

			$homePage = New \DOMDocument();
			$homePage->loadHTML( $this->http->get( $this->url ) );

			foreach( $homePage->getElementsByTagName( 'link' ) as $element ){
				if( $element->attributes->getNamedItem('rel')->nodeValue == 'EditURI' ){
					$rsdUrl = $element->attributes->getNamedItem('href')->nodeValue;
					$this->apiUrl = str_replace( '?action=rsd', '', $rsdUrl );
					break;
				}
			}

		}
		return $this->apiUrl;
	}

}