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
	 * @param bool $getCache are we to care about any caching stuff?
	 * @return Array of the unserialized returning data
	 */
	public function doRequest( ApiRequest &$request, $getCache = true ) {
		$gotCached = false;
		if( $getCache === true && $request->maxCacheAge() > 0 ){
			if( Cache::has( $request ) ){
				if( Cache::age( $request ) < $request->maxCacheAge() ){
					$request->setResult( Cache::get( $request ) );
					$gotCached = true;
				}
			}
		}

		if( $getCache === false || $gotCached === false ){
			if ( $request->isPost() ) {
				$request->setResult( json_decode( $this->http->post( $this->getUrl(), $request->getParameters() ), true ) );
			} else {
				$requestUrl = $this->getUrl() . "?" . http_build_query( $request->getParameters() );
				$request->setResult( json_decode( $this->http->get( $requestUrl ), true ) );
			}
			if( $gotCached === false && $request->maxCacheAge() > 0 ){
				Cache::add( $request );
			}
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

	/**
	 * Returns the data defined in the constructor
	 */
	public function doRequest( ApiRequest &$request, $getCache = null) {
		$request->setResult( json_decode( $this->testResult, true ) );
		return $request->getResult();
	}

}