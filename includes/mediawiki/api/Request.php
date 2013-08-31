<?php

namespace Addframe\Mediawiki\Api;

use Addframe\Cacheable;
use Addframe\Http;

// Some default cache lengths in seconds
const CACHE_WEEK = 604800;
const CACHE_DAY = 86400;
const CACHE_HOUR = 3600;
const CACHE_MINUTE = 60;
const CACHE_NONE = 0;

/**
 * Class Request representing a single api request
 */

class Request implements Cacheable{

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
	 * @param array $allowedParams optional set of parameters to limit the request to
	 * @throws \UnexpectedValueException
	 */
	function __construct( $params = array(), $shouldBePosted = false, $maxAge = CACHE_NONE, $allowedParams = array() ) {
		//make sure our construction params are correct
		if( !is_array( $params ) || !is_bool( $shouldBePosted ) || !is_int( $maxAge ) || !is_array( $allowedParams ) ){
			throw new \UnexpectedValueException( 'Request construction params are not of the correct types' );
		}

		$this->addAllowedParams( $allowedParams );

		// Only restrict params if a child class has said we should (ie we have some allowed params already set)
		// This means that this class can be used to perform requests with ANY params if it is not extended
		if( count( $this->allowedParams ) !== 0 ){
			$this->addAllowedParams( array( 'format' ) );
		}

		// Mediawiki is apparently moving towards json only, so lets just use it...
		$this->addParams( array( 'format' => 'json' ) );


		if( is_array( $params ) ){
			foreach( $params as $param => $value ) {
				// If one of the param values is an array, implode it to the form used by the api
				if ( is_array( $value ) ) {
					$params[ $param ] = implode( '|', $value );
				}
			}
		}

		$this->addParams( $params );
		$this->shouldBePosted = $shouldBePosted;
		$this->maxCacheAge = $maxAge;
	}

	/**
	 * Add each param to our array
	 * We don't want to set the array to $params in case other params have already been set (hence 'add' not 'set')
	 * @param $params array
	 */
	protected function addParams( $params ) {
		if( is_array( $params ) ){
			foreach( $params as $param => $value ){
				$this->params[ $param ] = $value;
			}
		}
	}

	/**
	 * Add parameters that are allowed to an array to be used in later validation / tidy up
	 * @param $params array
	 */
	protected function addAllowedParams( $params ) {
		$this->allowedParams = array_merge( $this->allowedParams, $params );
	}

	/**
	 * Remove null not allowed params
	 */
	protected function stripBadParams(){
		foreach( $this->params as $param => $value ){
			if ( !empty( $this->allowedParams ) && !in_array( $param, $this->allowedParams ) ){
				unset( $this->params[ $param ] );
			}
		}
	}

	/**
	 * @return bool should this request be posted
	 */
	public function shouldBePosted(){
		return $this->shouldBePosted;
	}

	/**
	 * @return array of set parameters
	 */
	public function getParameters(){
		$this->stripBadParams();
		return $this->params;
	}

	/**
	 * @param $param string param name
	 * @param $value string param value
	 * todo setting a param should check if value is an array and put it into a form used by the api
	 */
	public function setParameter( $param, $value ) {
		$this->params[ $param ] = $value;
	}

	/**
	 * @return mixed|null the result of the request
	 */
	public function getResult(){
		return $this->result;
	}

	/**
	 * @param $result mixed the result of the request (used to register the result)
	 */
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