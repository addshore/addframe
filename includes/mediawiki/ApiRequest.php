<?php

namespace Addframe\Mediawiki;

use Addframe\Cacheable;
use Addframe\Http;

// Some default cache lengths in seconds
const CACHE_WEEK = 604800;
const CACHE_DAY = 86400;
const CACHE_HOUR = 3600;
const CACHE_MINUTE = 60;
const CACHE_NONE = null;

class ApiRequest implements Cacheable{

	protected $result = null;
	protected $params = array();
	protected $cache = CACHE_NONE;
	protected $shouldBePosted = false;

	/**
	 * @param array $params Parameters for the api request
	 * @param bool $shouldBePosted Should be we a HTTP POST?
	 * @param bool|int $maxAge should be cache / how long to cache for in mins
	 */
	function __construct( $params = array(), $shouldBePosted = false, $maxAge = CACHE_NONE ) {
		$params['format'] = 'json';

		foreach( $params as $param => $value ) {
			if ( is_null( $value ) ){
				unset( $params[ $param ] );
			} else if ( is_array( $value ) ) {
				$params[ $param ] = implode( '|', $value );
			}
		}

		$this->params = $params;
		$this->shouldBePosted = $shouldBePosted;
		$this->cache = $maxAge;
	}

	public function isPost(){
		return $this->shouldBePosted;
	}

	public function getParameters(){
		return $this->params;
	}

	public function getResult(){
		return $this->result;
	}

	public function setResult( $result ){
		$this->result = $result;
	}

	public function getHash(){
		$hash = sha1( json_encode( $this->params ) );
		return 'ApiRequest_'.$hash;
	}

	public function getCacheData(){
		return $this->result;
	}

	public function maxCacheAge(){
		return $this->cache;
	}

}