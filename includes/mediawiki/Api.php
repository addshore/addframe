<?php

namespace Addframe\Mediawiki;

use Addframe\Http;

class Api {

	private $url;
	/**
	 * @var Http $http
	 */
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

	/**
	 * Performs a request to the api given the query and post data
	 * @param ApiRequest $request
	 * @throws \UnexpectedValueException
	 * @return Array of the unserialized returning data
	 */
	public function doRequest( ApiRequest $request ) {
		$data['format'] = $request->getFormat();

		//@todo this should be done in ApiRequest
		// Normalize the request data
		foreach( $data as $param => $value ) {
			if ( is_array( $value ) ) {
				$data[ $param ] = implode( '|', $value );
			}
		}

		if ( $request->isPost() ) {
			$result = $this->http->post( $this->getUrl(), $data );
			return $this->unserializeResult( $result, $request->getFormat() );
		} else {
			$result = $this->http->get( $this->getUrl() . "?" . http_build_query( $data ) );
			return $this->unserializeResult( $result, $request->getFormat() );
		}
	}

	private function unserializeResult( $result, $format ) {

		//todo wddx, xml, yaml, rawfm, txt, dbg, dump
		//todo accept all formatted versions, phpfm, jsonfm

		switch ( $format ) {
			case 'php':
				return unserialize( $result );
				break;
			case 'json':
				return json_decode( $result, true );
				break;
		}

		throw new \UnexpectedValueException( "Api can not unserialize a result in format '{$format}'" );
	}

}