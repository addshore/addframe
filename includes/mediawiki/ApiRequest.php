<?php

namespace Addframe\Mediawiki;

use Addframe\Http;

const CACHE_WEEK = 10080;
const CACHE_DAY = 1440;
const CACHE_HOUR = 60;
const CACHE_NONE = false;

class ApiRequest {

	protected $result = null;
	protected $params = array();
	protected $cache = CACHE_NONE;
	protected $shouldBePosted = false;

	/**
	 * @param array $params Parameters for the api request
	 * @param bool $shouldBePosted Should be we a HTTP POST?
	 * @param bool|int $cache should be cache / how long to cache for in mins
	 */
	function __construct( $params = array(), $shouldBePosted = false, $cache = CACHE_NONE ) {
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
		$this->cache = $cache;
	}

	public function isCacheable(){
		return $this->cache;
	}

	public function isPost(){
		return $this->shouldBePosted;
	}

	public function getParameters(){
		return $this->params;
	}

	public function execute( Api $api ){
		$this->result = $api->doRequest( $this );
		return $this->result;
	}

	public function getResult(){
		return $this->result;
	}

	public function setResult( $result ){
		$this->result = $result;
	}

	public function getHash(){
		$hash = sha1( json_encode( $this->params ) );
		return $hash;
	}

	public function cache(){

	}
}