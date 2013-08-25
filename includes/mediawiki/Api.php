<?php

namespace Addframe\Mediawiki;

use Addframe\Cache;
use Addframe\Http;

/**
 * Class Api representing a Mediawiki API
 * @package Addframe\Mediawiki
 */

class Api {

	private $url;
	/**
	 * @var Http $http
	 */
	private $http;

	/**
	 * This should generally not be used, use Api::new* instead
	 */
	/* protected */ function __construct( $http = null ) {
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

	/**
	 * Creates a new API class using the given url
	 * @param $url string to create the class with
	 * @return Api
	 */
	public static function newFromUrl( $url ){
		$site = new Api( );
		$site->setUrl( $url );
		return $site;
	}

	/**
	 * Gets a result for the given API request either by requesting it or using cached data
	 * @param ApiRequest $request
	 * @param bool $getCache do we want to check in the cache for a result?
	 * @return Array of the unserialized result data
	 */
	public function doRequest( ApiRequest &$request, $getCache = true ) {
		$gotCached = false;
		if( $getCache === true && $request->maxCacheAge() > 0 ){
			try{
			if( Cache::has( $request ) ){
				if( Cache::age( $request ) < $request->maxCacheAge() ){
					$request->setResult( Cache::get( $request ) );
					$gotCached = true;
				}
			}
			} catch( \IOException $e ){
				//log that caching had an error!
			}
		}

		if( $getCache === false || $gotCached === false ){
			if ( $request->shouldBePosted() ) {
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

	public function doRequestWithToken( ApiRequest &$request, $tokenType = 'edittoken', $getCache = true ) {
		//todo the above hackery makes me think api and site should be the same class
		//todo then we could use getToken('edit') instead...
		$tokenResult = $this->doRequest( new TokensRequest( $tokenType ), $getCache );
		$request->setParameter( 'token', $tokenResult['tokens'][$tokenType] );
		return $this->doRequest( $request, false );
	}

}

/**
 * Class TestApi, Overrides used methods in Api so we can return some default data
 * @package Addframe
 */
class TestApi extends Api{

	protected $returnData;

	/**
	 * @param array|string $returnData array of data to return in json form
	 */
	function __construct( $returnData = '' ) {
			$this->returnData = $returnData;
	}

	/**
	 * Returns the data defined in the constructor
	 */
	public function doRequest( ApiRequest &$request, $getCache = null) {
		if( is_array( $this->returnData ) ){
			$testResult = array_shift( $this->returnData );
		} else {
			$testResult = $this->returnData;
		}
		$request->setResult( json_decode( $testResult, true ) );
		return $request->getResult();
	}

}