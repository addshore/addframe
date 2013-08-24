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
		if ( $request->isPost() ) {
			$result = $this->http->post( $this->getUrl(), $request->getParameters() );
			return json_decode( $result, true );
		} else {
			$result = $this->http->get( $this->getUrl() . "?" . http_build_query( $request->getParameters() ) );
			return json_decode( $result, true );
		}
	}

}

/**
 * Class TestApi, Overrides used methods in Api so we can return some default data
 * @package Addframe
 */
class TestApi extends Api{

	protected $testResult;

	/**
	 * @param string $returnData in json form
	 */
	function __construct( $returnData = '' ) {
		$this->testResult = $returnData;
	}

	public function doRequest( ApiRequest $request ) {
		return json_decode( $this->testResult, true );
	}

}