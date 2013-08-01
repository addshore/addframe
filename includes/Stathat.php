<?php

namespace Addframe;

class Stathat {

	protected $key;

	function __construct( $key ) {
		$this->key = $key;
	}

	private function do_post_request( $url, $data, $optional_headers = null ) {
		$params = array( 'http' => array( 'method' => 'POST', 'content' => $data ) );
		if ( $optional_headers !== null ) {
			$params['http']['header'] = $optional_headers;
		}
		$ctx = stream_context_create( $params );
		$fp = @fopen( $url, 'rb', false, $ctx );
		if ( ! $fp ) {
			throw new \Exception( "Problem with $url, $php_errormsg" );
		}
		$response = @stream_get_contents( $fp );
		if ( $response === false ) {
			throw new \Exception( "Problem reading data from $url, $php_errormsg" );
		}
		return $response;
	}

	private function do_async_post_request( $url, $params ) {
		$post_params = array();
		foreach ( $params as $key => &$val ) {
			if ( is_array( $val ) )
				$val = implode( ',', $val );
			$post_params[] = $key . '=' . urlencode( $val );
		}
		$post_string = implode( '&', $post_params );

		$parts = parse_url( $url );

		$fp = fsockopen( $parts['host'], isset( $parts['port'] ) ? $parts['port'] : 80, $errno, $errstr, 30 );

		$out = "POST " . $parts['path'] . " HTTP/1.1\r\n";
		$out .= "Host: " . $parts['host'] . "\r\n";
		$out .= "Content-Type: application/x-www-form-urlencoded\r\n";
		$out .= "Content-Length: " . strlen( $post_string ) . "\r\n";
		$out .= "Connection: Close\r\n\r\n";
		if ( isset( $post_string ) )
			$out .= $post_string;

		fwrite( $fp, $out );
		fclose( $fp );
	}

	public function stathat_ez_count( $stat_name, $count ) {
		$this->do_async_post_request( "http://api.stathat.com/ez", array( 'email' => $this->key , 'stat' => $stat_name, 'count' => $count ) );
		//echo "StatHat - ".$stat_name." - Added count - '$count'\n";
	}

	public function stathat_ez_value( $stat_name, $value ) {
		$this->do_async_post_request( "http://api.stathat.com/ez", array( 'email' => $this->key , 'stat' => $stat_name, 'value' => $value ) );
		//echo "StatHat - ".$stat_name." - Added value - '$value'\n";
	}

	public function stathat_ez_count_sync( $stat_name, $count ) {
		return $this->do_post_request( "http://api.stathat.com/ez", "email=$this->key&stat=$stat_name&count=$count" );
	}

	public function stathat_ez_value_sync( $stat_name, $value ) {
		return $this->do_post_request( "http://api.stathat.com/ez", "email=$this->key&stat=$stat_name&value=$value" );
	}

}