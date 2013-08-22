<?php

namespace Addframe\Wiki;

class Site {

	var $url;

	public function __construct() {
	}

	public function setUrl( $url ){
		$this->url = $url;
	}

	public function getUrl(){
		return $this->url;
	}

	public static function newFromUrl( $url ){
		$site = new Site;
		$site->setUrl( $url );
	}

}