<?php

namespace Addframe\Mediawiki;

use Addframe\Http;

class ApiRequest {

	private $cacheable = false;
	private $format = 'php';
	private $post = false;
	private $data = array();

	function __construct( $data = array(), $post = false, $format = 'php', $cacheable = false ) {
		$this->data = $data;
		$this->post = $post;
		$this->format = $format;
		$this->cacheable = $cacheable;
	}

	public function isCacheable(){
		return $this->cacheable;
	}

	public function isPost(){
		return $this->post;
	}

	public function getFormat(){
		return $this->format;
	}

	public function getData(){
		return $this->data;
	}

}