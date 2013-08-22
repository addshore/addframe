<?php

namespace Addframe\Mediawiki;

/**
 * Class Site - Represents a mediawiki site
 * @package Addframe\Mediawiki
 */

class Site {

	protected $url = null;
	protected $apiUrl = null;

	public function __construct() {
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
		return $this->apiUrl;
	}

	public static function newFromUrl( $url ){
		$site = new Site;
		$site->setUrl( $url );
	}

}