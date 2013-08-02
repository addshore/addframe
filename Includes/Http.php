<?php

namespace Addframe;

/**
 * This class is designed to provide a simplified interface to cURL which maintains cookies.
 * @since 0.0.1
 * @author Cobi
 * @author Addshore
 **/

class Http {
	protected $ch;
	protected $uid;
	public $cookie_jar;
	public $postfollowredirs;
	public $getfollowredirs;

	/**
	 * Do you want to echo each request? (good for debuging)
	 * @todo this should be included is a global setting of some kind
	 * @var bool
	 */
	public $quiet = true;

	/**
	 * @param $data array of data to be encoded
	 * @param string $keyprefix
	 * @param string $keypostfix
	 * @return null|string
	 */
	public function data_encode( $data, $keyprefix = "", $keypostfix = "" ) {
		assert( is_array( $data ) );
		$vars = null;
		foreach ( $data as $key => $value ) {
			if ( is_array( $value ) )
				$vars .= $this->data_encode( $value, $keyprefix . $key . $keypostfix . urlencode( "[" ), urlencode( "]" ) ); else
				$vars .= $keyprefix . $key . $keypostfix . "=" . urlencode( $value ) . "&";
		}
		return $vars;
	}

	function __construct() {
		$this->ch = curl_init();
		$this->uid = dechex( rand( 0, 99999999 ) );
		curl_setopt( $this->ch, CURLOPT_COOKIEJAR, '/tmp/addframe.cookies.' . $this->uid . '.dat' );
		curl_setopt( $this->ch, CURLOPT_COOKIEFILE, '/tmp/addframe.cookies.' . $this->uid . '.dat' );
		curl_setopt( $this->ch, CURLOPT_MAXCONNECTS, 100 );
		curl_setopt( $this->ch, CURLOPT_CLOSEPOLICY, CURLCLOSEPOLICY_LEAST_RECENTLY_USED );
		$this->postfollowredirs = 0;
		$this->getfollowredirs = 1;
		$this->cookie_jar = array();
	}

	/**
	 * @param $url string url of request
	 * @param $data array of data to post key => value
	 * @return string result of request
	 */
	function post( $url, $data ) {
		$time = microtime( 1 );
		curl_setopt( $this->ch, CURLOPT_URL, $url );
		curl_setopt( $this->ch, CURLOPT_USERAGENT, 'Addframe Mediawiki bot' );
		curl_setopt( $this->ch, CURLOPT_ENCODING, "UTF-8" );
		/* Crappy hack to add extra cookies, should be cleaned up */
		$cookies = null;
		foreach ( $this->cookie_jar as $name => $value ) {
			if ( empty( $cookies ) )
				$cookies = "$name=$value"; else
				$cookies .= "; $name=$value";
		}
		if ( $cookies != null )
			curl_setopt( $this->ch, CURLOPT_COOKIE, $cookies );
		curl_setopt( $this->ch, CURLOPT_FOLLOWLOCATION, $this->postfollowredirs );
		curl_setopt( $this->ch, CURLOPT_MAXREDIRS, 10 );
		curl_setopt( $this->ch, CURLOPT_HTTPHEADER, array( 'Expect:' ) );
		curl_setopt( $this->ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt( $this->ch, CURLOPT_TIMEOUT, 30 );
		curl_setopt( $this->ch, CURLOPT_CONNECTTIMEOUT, 10 );
		curl_setopt( $this->ch, CURLOPT_POST, 1 );
		curl_setopt( $this->ch, CURLOPT_POSTFIELDS, $data );
		$data = curl_exec( $this->ch );
		if ( ! $this->quiet )
			echo 'POST: ' . $url . ' (' . ( microtime( 1 ) - $time ) . ' s) (' . strlen( $data ) . " b)\n";
		return $data;
	}

	/**
	 * @param $url string url to get
	 * @return string result of request
	 */
	function get( $url ) {
		$time = microtime( 1 );
		curl_setopt( $this->ch, CURLOPT_URL, $url );
		curl_setopt( $this->ch, CURLOPT_USERAGENT, 'Addframe Mediawiki bot' );
		curl_setopt( $this->ch, CURLOPT_ENCODING, "UTF-8" );
		/* Crappy hack to add extra cookies, should be cleaned up */
		$cookies = null;
		foreach ( $this->cookie_jar as $name => $value ) {
			if ( empty( $cookies ) )
				$cookies = "$name=$value"; else
				$cookies .= "; $name=$value";
		}
		if ( $cookies != null )
			curl_setopt( $this->ch, CURLOPT_COOKIE, $cookies );
		curl_setopt( $this->ch, CURLOPT_FOLLOWLOCATION, $this->getfollowredirs );
		curl_setopt( $this->ch, CURLOPT_MAXREDIRS, 10 );
		curl_setopt( $this->ch, CURLOPT_HEADER, 0 );
		curl_setopt( $this->ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt( $this->ch, CURLOPT_TIMEOUT, 30 );
		curl_setopt( $this->ch, CURLOPT_CONNECTTIMEOUT, 10 );
		curl_setopt( $this->ch, CURLOPT_HTTPGET, 1 );
		$data = curl_exec( $this->ch );
		if ( ! $this->quiet )
			echo 'GET: ' . $url . ' (' . ( microtime( 1 ) - $time ) . ' s) (' . strlen( $data ) . " b)\n";

		return $data;
	}

	/**
	 * @param $uname string
	 * @param $pwd string
	 */
	function setHTTPcreds( $uname, $pwd ) {
		curl_setopt( $this->ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC );
		curl_setopt( $this->ch, CURLOPT_USERPWD, $uname . ":" . $pwd );
	}

	function __destruct() {
		curl_close( $this->ch );
		@unlink( '/tmp/addframe.cookies.' . $this->uid . '.dat' );
	}
}