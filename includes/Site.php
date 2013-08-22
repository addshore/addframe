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
	protected $http;

	public function __construct() {
		$this->http = Http::getDefaultInstance();
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
			$this->getApiUrlFromIndex();
		}
		return $this->apiUrl;
	}

	public static function newFromUrl( $url ){
		$site = new Site( new Http() );
		$site->setUrl( $url );
		return $site;
	}

	private function getApiUrlFromIndex() {
		if( !is_null( $this->url ) ){
			//todo get the api url from the url
		}
	}

}