<?php

namespace Addframe\Mediawiki;

use Addframe\Cache;
use Addframe\CacheException;
use Addframe\Http;
use Addframe\HttpException;
use Addframe\Logger;
use Addframe\Mediawiki\Api\Request;
use Addframe\Mediawiki\Api\TokensRequest;
use Addframe\Mediawiki\Api\UsageException;

/**
 * Class Api representing a Mediawiki API
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

	/**
	 * @param $url string
	 */
	public function setUrl( $url ) {
		$this->url = $url;
	}

	/**
	 * @return mixed string
	 */
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
	 * @param Request $request
	 * @param bool $getCache do we want to check in the cache for a result?
	 * @throws UsageException
	 * @throws \UnexpectedValueException
	 * @return Array of the unserialized result data
	 */
	public function doRequest( Request &$request, $getCache = true ) {
		$result = null;

		//try to get a cached value if we should
		if( $getCache === true && $request->maxCacheAge() > 0 ){
			try{
				if( Cache::has( $request ) ){
					if( Cache::age( $request ) < $request->maxCacheAge() ){
						$result = Cache::get( $request );
						$request->setResult( $result );
					}
				}
			} catch( CacheException $e ){
				Logger::logError( $e->getMessage() );
			}
		}

		//otherwise do a real request
		if( is_null( $result ) ){
			try{
				if ( $request->shouldBePosted() ) {
					$requestUrl = $this->getUrl();
					$httpResponse = $this->http->post( $requestUrl, $request->getParameters() );
					$result = json_decode( $httpResponse, true );
					$request->setResult( $result );
				} else {
					$requestUrl = $this->getUrl() . "?" . http_build_query( $request->getParameters() );
					$httpResponse = $this->http->get( $requestUrl );
					$result = json_decode( $httpResponse, true );
					$request->setResult( $result );
				}
			} catch( HttpException $e ){
				Logger::logError( $e->getMessage() );
			}

			//try to cache the new request if we want to
			if( $request->maxCacheAge() > 0 ){
				try{
					Cache::add( $request );
				} catch( CacheException $e ){
					Logger::logError( $e->getMessage() );
				}
			}
		}

		if( !is_array( $result ) ){
			throw new \UnexpectedValueException( 'Api result should be an array, instead is ' . print_r( $result ) );
		} else if( array_key_exists( 'error', $result ) ){
			throw new UsageException( $result['error'] );
		}

		return $result;
	}

	/**
	 * Do a request with a token
	 * @param Request $request
	 * @param string $tokenType
	 * @param bool $getCache
	 * @throws UsageException
	 * @return Array
	 */
	public function doRequestWithToken( Request &$request, $tokenType = 'edittoken', $getCache = true ) {
		//todo the below hackery makes me think api and site should be the same class
		//todo then we could use getToken('edit') instead...

		/**
		 * getting the tokens in this way should be okay as caching should mean that we
		 * do not actually make a new request to the api for a token each time...
		 */
		try{
			$tokenResult = $this->doRequest( new TokensRequest( array( 'type' => $tokenType ) ), $getCache );
			$request->setParameter( 'token', $tokenResult['tokens'][$tokenType] );
			return $this->doRequest( $request, false );
		} catch( UsageException $e ){
			$code = $e->getCodeString();
			if( $code == 'badtoken' || $code == 'notoken' ){
				Logger::logWarn( 'UsageException' . $e->__toString() );
			} else {
				throw $e;
			}
		}
		return null;
	}

}

/**
 * Class TestApi, Overrides used methods in Api so we can return some default data
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
	public function doRequest( Request &$request, $getCache = null) {
		if( is_array( $this->returnData ) ){
			$testResult = array_shift( $this->returnData );
		} else {
			$testResult = $this->returnData;
		}
		$request->setResult( json_decode( $testResult, true ) );
		return $request->getResult();
	}

}