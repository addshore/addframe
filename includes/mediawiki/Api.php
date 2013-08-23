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
	 * @param Array $data of request
	 * @param Array|bool $post data of request
	 * @throws \UnexpectedValueException
	 * @return Array of the unserialized returning data
	 */
	public function doRequest( $data, $post = null ) {
		if( !is_array( $data ) || ( !is_null( $post ) && !is_array( $post ) ) ){
			throw new \UnexpectedValueException( 'Array of data expected for Api Request' );
		}

		$data['format'] = 'php';

		// Normalize some stuff
		foreach( $data as $param => $value ) {
			if ( is_array( $value ) ) {
				$data[ $param ] = implode( '|', $value );
			}
		}

		$query = "?" . http_build_query( $data );
		$query = $this->getUrl() . $query;
		if ( is_null( $post ) ) {
			$html = $this->http->get( $query );
			return unserialize( $html );
		} else {
			$html = $this->http->post( $query, $post );
			return unserialize( $html );
		}
	}

}