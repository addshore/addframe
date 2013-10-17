<?php

namespace Addframe\Mediawiki;

use Addframe\Http;
use Addframe\HttpException;
use Addframe\Logger;
use Addframe\Mediawiki\Api\Request;
use Addframe\Mediawiki\Api\TokensRequest;
use Addframe\Mediawiki\Api\UsageException;
use UnexpectedValueException;

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
	 * @var array of tokens for this api
	 */
	private $tokens = array();

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
	 * Gets a result for the given API request
	 *
	 * @param Request $request
	 *
	 * @throws UnexpectedValueException
	 * @throws UsageException
	 * @return Array of the unsterilized result data
	 */
	public function doRequest( Request &$request ) {
		$result = null;

		//otherwise do a real request
		if( is_null( $result ) ){
			try{
				if ( $request->shouldPost() ) {
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

		}

		if( !is_array( $result ) ){
			throw new UnexpectedValueException( 'Api result should be an array, instead is ' . print_r( $result ) );
		} else if( array_key_exists( 'error', $result ) ){
			$exception = new UsageException( $result['error'] );
			Logger::logWarn( 'UsageException ' . $exception->__toString() );
			throw $exception;
		}

		return $result;
	}

	/**
	 * Do a request with a token
	 *
	 * @param Request $request
	 * @param string $tokenType
	 *
	 * @return array
	 */
	public function doRequestWithToken( Request &$request, $tokenType = 'edit' ) {
		if( !array_key_exists( $tokenType, $this->tokens ) ){
			$tokenResult = $this->doRequest( new TokensRequest( array ( 'type' => $tokenType ) ) );
			$this->tokens[ $tokenType ] = $tokenResult['tokens'][$tokenType.'token'];
		}
		$token = $this->tokens[ $tokenType ];
		$request->setParameter( 'token', $token );
		$result = $this->doRequest( $request );
		return $result;
	}

}

/**
 * Class TestApi, Overrides used methods in Api so we can return some default data
 */
class TestApi extends Api{

	/**
	 * @var array|string the store of data to return from the api
	 */
	protected $returnData;
	/**
	 * @var Request[] of requests the api has made (this can be used to check params)
	 */
	public $completeRequests = array();

	/**
	 * @param array|string $returnData array of data to return in json form
	 */
	public function __construct( $returnData = '' ) {
			$this->returnData = $returnData;
	}

	/**
	 * Returns the data defined in the constructor
	 * @param Request $request
	 * @return Array|mixed|null
	 */
	public function doRequest( Request &$request) {
		$this->completeRequests[] = $request;
		if( is_array( $this->returnData ) ){
			$testResult = array_shift( $this->returnData );
		} else {
			$testResult = $this->returnData;
		}
		$request->setResult( json_decode( $testResult, true ) );
		return $request->getResult();
	}

}