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

			$html = $this->http->get( $this->url );
			preg_match( '/\<link rel=\"EditURI.*?$/im', $html, $scrape1 );
			if ( ! isset( $scrape1[0] ) ) {
				throw new \OutOfBoundsException( "Undefined offset when scraping ApiUrl pt1" );
			}
			preg_match( '/href=\"([^\"]+)\"/i', $scrape1[0], $scrape2 );
			if ( ! isset( $scrape2[1] ) ) {
				throw new \OutOfBoundsException( "Undefined offset when scraping ApiUrl pt2" );
			}

			$parsedApiUrl = parse_url( $scrape2[1] );

			//Note: The below is back compatability check for the parse_url function
			if( array_key_exists('host', $parsedApiUrl) ){
				$this->apiUrl = $parsedApiUrl['host'] . $parsedApiUrl['path'];
			} else {
				//pre 5.4.7
				$this->apiUrl = trim($parsedApiUrl['path'] ,'/');
			}

		}
		return $this->apiUrl;
	}

}