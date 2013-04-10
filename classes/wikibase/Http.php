<?php

/**
 * Http related code.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 * http://www.gnu.org/copyleft/gpl.html
 *
 * @file
 *
 * @licence GNU GPL v2+
 * @author Thomas Pellissier Tanon
 */
class Httpwb {
	protected $ch;
	protected $uid;

	public function __construct( $userAgent = 'BaseBot' ) {
		$this->ch = curl_init();
		$this->uid = dechex( rand( 0,99999999 ) );
		curl_setopt( $this->ch, CURLOPT_USERAGENT, $userAgent );
		curl_setopt( $this->ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $this->ch, CURLOPT_FOLLOWLOCATION, true );
		curl_setopt( $this->ch,CURLOPT_COOKIEJAR, 'cookies.' . $this->uid . '.dat' );
		curl_setopt( $this->ch,CURLOPT_COOKIEFILE, 'cookies.' . $this->uid . '.dat' );
	}

	public function __destruct() {
		curl_close( $this->ch );
		@unlink( 'cookies.' . $this->uid . '.dat' );
	}

	/**
	 * @param string $url the url
	 * @return string the content
	 * @throws Exception
	 */
	public function get( $url ) {
		curl_setopt( $this->ch, CURLOPT_URL, $url );
		curl_setopt( $this->ch,CURLOPT_HTTPGET, true );
		$response = curl_exec( $this->ch );
		if( curl_errno( $this->ch ) ) {
			throw new Exception( curl_error( $this->ch ), curl_errno( $this->ch ) );
		} else if( curl_getinfo( $this->ch, CURLINFO_HTTP_CODE) >= 400 ) {
			throw new Exception( 'HTTP error: ' . $url, curl_getinfo( $this->ch, CURLINFO_HTTP_CODE ) );
		}
		return $response;
	}

	/**
	 * @param string $url the url
	 * @param string[] $postFields field to put in the post request
	 * @return string the content
	 * @throws Exception
	 */
	public function post( $url, $postFields ) {
		curl_setopt( $this->ch, CURLOPT_URL, $url );
		curl_setopt( $this->ch, CURLOPT_POST, true );
		curl_setopt( $this->ch, CURLOPT_POSTFIELDS, $postFields );
		$response = curl_exec( $this->ch );
		if( curl_errno( $this->ch ) ) {
			throw new Exception( curl_error( $this->ch ), curl_errno( $this->ch ) );
		} else if( curl_getinfo( $this->ch, CURLINFO_HTTP_CODE) >= 400 ) {
			throw new Exception( 'HTTP error: ' . $url, curl_getinfo( $this->ch, CURLINFO_HTTP_CODE ) );
		}
		return $response;
	}
}
