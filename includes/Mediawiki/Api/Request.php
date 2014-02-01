<?php

namespace Addframe\Mediawiki\Api;

use UnexpectedValueException;

/**
 * Class Request representing a single api request
 */
class Request {

	/**
	 * @var null|mixed the result of the request
	 */
	protected $result = null;
	/**
	 * @var array the parameters of the request
	 */
	protected $params = array();
	/**
	 * @var bool should this api request be POSTed?
	 */
	protected $shouldBePosted = false;

	protected $allowedParams = array();

	/**
	 * This is also a fluid interface (see the bottom of this method)
	 *
	 * @param array $params Parameters for the api request
	 * @param bool $shouldPost Should be we a HTTP POST?
	 * @param array $allowedParams optional set of parameters to limit the request to
	 *
	 * @throws UnexpectedValueException
	 */
	public function __construct( $params = array (), $shouldPost = false, $allowedParams = array () ) {
		if( !is_array( $params ) || !is_bool( $shouldPost ) || !is_array( $allowedParams ) ){
			throw new UnexpectedValueException( 'Request construction params are not of the correct types' );
		}

		$this->addAllowedParams( $allowedParams );

		// Only restrict params if a child class has said we should (ie we have some allowed params already set)
		// This means that this class can be used to perform requests with ANY params if it is not extended
		if( count( $this->allowedParams ) !== 0 ){
			$this->addAllowedParams( array( 'format' ) );
		}

		if( is_array( $params ) ){
			foreach( $params as $param => $value ) {
				// If one of the param values is an array, implode it to the form used by the api
				if ( is_array( $value ) ) {
					$params[ $param ] = implode( '|', $value );
				}
			}
		}

		return $this
			->addParams( array( 'format' => 'json' ) ) // always use json
			->addParams( $params )
			->setShouldPost( $shouldPost );
	}

	/**
	 * Add each param to our array
	 * We don't want to set the array to $params in case other params have already been set (hence 'add' not 'set')
	 * @param $params array
	 * @return $this
	 */
	protected function addParams( $params ) {
		if( is_array( $params ) ){
			foreach( $params as $param => $value ){
				$this->params[ $param ] = $value;
			}
		}
		return $this;
	}

	/**
	 * @param $bool bool Should be we a HTTP POST?
	 * @return $this
	 */
	public function setShouldPost( $bool ){
		$this->shouldBePosted = $bool;
		return $this;
	}

	/**
	 * Add parameters that are allowed to an array to be used in later validation / tidy up
	 * @param $params array
	 * @return $this
	 */
	protected function addAllowedParams( $params ) {
		$this->allowedParams = array_merge( $this->allowedParams, $params );
		return $this;
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
	public function shouldPost(){
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

}