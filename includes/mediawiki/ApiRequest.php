<?php

namespace Addframe\Mediawiki;

use Addframe\Http;

class ApiRequest {

	private $format = 'php';
	private $params = array();

	function __construct( $params = array(), $format = 'php' ) {

		foreach( $params as $param => $value ){
			if( !$this->acceptsParameter( $param ) ){
				throw new \UnexpectedValueException( "ApiRequest does not expect parameter {$param}" );
			}
			//todo validate the values
		}

		foreach( $params as $param => $value ) {
			if ( is_array( $value ) ) {
				$params[ $param ] = implode( '|', $value );
			}
		}

		$this->params = $params;
		$this->format = $format;
	}

	public function isCacheable(){
		return false;
	}

	public function isPost(){
		return false;
	}

	public function getFormat(){
		return $this->format;
	}

	public function getParameters(){
		return $this->params;
	}

	protected function getAllowedParams(){
		return array();
	}

	public function acceptsParameter( $parameter ){
		if( in_array( $parameter, $this->getAllowedParams() ) || $this->getAllowedParams() === array() ){
			return true;
		} else {
			return false;
		}
	}

}