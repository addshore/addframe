<?php

namespace Addframe\Mediawiki;

use Addframe\Http;

class Api {

	private $url;
	private $http;

	function __construct( $http = null ) {
		if( is_null( $http ) ){
			$this->http = Http::getDefaultInstance();
		} else {
			$this->http = $http;
		}
	}

	public function setUrl( $url ) {
		$this->url = $url;
	}

	public function getUrl() {
		return $this->url;
	}

	public static function newFromUrl( $url ){
		$site = new Api( );
		$site->setUrl( $url );
		return $site;
	}

}