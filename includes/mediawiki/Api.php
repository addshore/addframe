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

		//todo support more decoding methods other than unserialize()

		if ( $request->isPost() ) {
			$html = $this->http->post( $this->getUrl(), $data );
			return unserialize( $html );
		} else {
			$html = $this->http->get( $this->getUrl() . "?" . http_build_query( $data ) );
			return unserialize( $html );
		}
	}

}