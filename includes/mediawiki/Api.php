<?php

namespace Addframe\Mediawiki;

use Addframe\Cache;
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
	 * @param bool $getCached
	 * @return Array of the unserialized returning data
	 */
	public function doRequest( ApiRequest $request, $getCached = true ) {
		if( !is_null( $request->cacheFor() ) && $getCached === true ){
			if( Cache::has( $request->getHash() ) ){
				//todo make sure the cache has not expired!
				$request->setResult( Cache::get( $request->getHash() ) );
			}
		}

		if( !isset( $result ) ){
			if ( $request->isPost() ) {
				$request->setResult( json_decode( $this->http->post( $this->getUrl(), $request->getParameters() ), true ) );
			} else {
				$requestUrl = $this->getUrl() . "?" . http_build_query( $request->getParameters() );
				$request->setResult( json_decode( $this->http->get( $requestUrl ), true ) );
			}
			Cache::add( $request );
		}

		return $request->getResult();
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

	public function doRequest( ApiRequest $request, $getCached = null) {
		$request->setResult( json_decode( $this->testResult, true ) );
		return $request->getResult();
	}

}