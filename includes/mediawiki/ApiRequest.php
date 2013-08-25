<?php

namespace Addframe\Mediawiki;

use Addframe\Cacheable;
use Addframe\Http;

// Some default cache lengths in seconds
const CACHE_WEEK = 604800;
const CACHE_DAY = 86400;
const CACHE_HOUR = 3600;
const CACHE_MINUTE = 60;
const CACHE_NONE = 0;

/**
 * Class ApiRequest representing a single api request
 * @package Addframe\Mediawiki
 */

class ApiRequest implements Cacheable{

	/**
	 * @var null|mixed the result of the request
	 */
	protected $result = null;
	/**
	 * @var array the parameters of the request
	 */
	protected $params = array();
	/**
	 * @var int maximum number of seconds to cache a result of this request for
	 */
	protected $maxCacheAge = CACHE_NONE;
	/**
	 * @var bool should this api request be POSTed?
	 */
	protected $shouldBePosted = false;

	protected $allowedParams = array();

	/**
	 * @param array $params Parameters for the api request
	 * @param bool $shouldBePosted Should be we a HTTP POST?
	 * @param bool|int $maxAge should be cache / how long to cache for in seconds
	 */
	function __construct( $params = array(), $shouldBePosted = false, $maxAge = CACHE_NONE ) {
		// Only restrict params if a child class has said we should
		// This means that this class can be used to perform requests with ANY params
		if( count( $this->allowedParams ) !== 0 ){
			$this->addAllowedParams( array( 'format' ) );
		}

		$this->addParams( array( 'format' => 'json' ) );

		if( is_array( $params ) ){
			foreach( $params as $param => $value ) {
				if ( is_array( $value ) ) {
					$params[ $param ] = implode( '|', $value );
				}
			}
		}

		$this->addParams( $params );
		$this->stripBadParams();

		$this->shouldBePosted = $shouldBePosted;
		$this->maxCacheAge = $maxAge;
	}

	protected function addParams( $params ) {
		if( is_array( $params ) ){
			foreach( $params as $param => $value ){
				$this->params[ $param ] = $value;
			}
		}
	}

	protected function addAllowedParams( $params ) {
		$this->allowedParams = array_merge( $this->allowedParams, $params );
	}

	protected function stripBadParams(){
		foreach( $this->params as $param => $value ){
			if ( is_null( $value ) || ( !empty( $this->allowedParams ) && !in_array( $param, $this->allowedParams ) ) ){
				unset( $this->params[ $param ] );
			}
		}
	}

	public function shouldBePosted(){
		return $this->shouldBePosted;
	}

	public function getParameters(){
		return $this->params;
	}

	public function setParameter( $param, $value ) {
		$this->params[ $param ] = $value;
	}

	public function getResult(){
		return $this->result;
	}

	public function setResult( $result ){
		$this->result = $result;
	}

	/**
	 * @see Cacheable::getHash()
	 */
	public function getHash(){
		$hash = sha1( json_encode( $this->params ) );
		return 'ApiRequest_'.$hash;
	}

	/**
	 * @see Cacheable::getCacheData()
	 */
	public function getCacheData(){
		return $this->result;
	}

	/**
	 * @see Cacheable::maxCacheAge()
	 */
	public function maxCacheAge(){
		return $this->maxCacheAge;
	}

}