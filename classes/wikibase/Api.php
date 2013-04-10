<?php

/**
 * Base API class.
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
class Api {
	protected $http;
	protected $wiki;
	protected $editToken;
	protected $botEdits = true;
	protected $editLimit = true;
	protected $lastEditTimestamp = 0;
	protected $editLaps = 2;

	/**
	 * @param string $wiki the base url of the wiki like "fr.wikipedia.org"
	 * @param string $agent user agent for HTTP requests. The user agent of the lib is added at the end of the string
	 * @param bollean $editLimit limit the number of edit each second
	 * @param Http|null $http the http object to use. By default a new object is created
	 */
	public function __construct( $wiki, $agent, $editLimit = true, $http = null ) {
		$this->http = ($http instanceof Httpwb) ? $http : new Httpwb( trim( $agent ) . ' WikibasePhpLib/0.1' );
		$this->wiki = $wiki;
		$this->editLimit = $editLimit;
	}

	/**
	 * @param bollean $param tags all edits as bot or not. By default true
	 */
	public function useBotEdits( $param = true ) {
		$this->botEdits = $param;
	}

	/**
	 * @param string[] $params parameter to put in the url
	 * @return array the API result
	 * @throws Exception
	 */
	public function get( $params ) {
		$params['format'] = 'json';
		$url = $this->wiki . '/w/api.php?' . http_build_query($params);
		$response = $this->http->get( $url );
		$result = json_decode( $response, true );
		if( isset( $result['error'] ) ) {
			throw new Exception( $result['error']['info'] );
		}
		return $result;
	}

	/**
	 * @param string[] $params parameter to put in the url
	 * @param string[] $postFields field to put in the post request
	 * @return array the API result
	 * @throws Exception
	 */
	public function post( $params, $postFields ) {
		$params['format'] = 'json';
		$url = $this->wiki . '/w/api.php?' . http_build_query($params);
		$response = $this->http->post( $url, $postFields );
		$result = json_decode( $response, true );
		if( isset( $result['error'] ) ) {
			throw new Exception( $result['error']['info'] );
		}
		return $result;
	}

	/**
	 * Get the continuation parameter of a query
	 * @param array $result the result of the query
	 * @return array( continuation parameter key, continuation parameter value )
	 */
	public function getContinueParam( $result ) {
		if( array_key_exists( 'query-continue', $result ) ) {
			$keys = array_keys( $result['query-continue'] );
			$keys2 = array_keys( $result['query-continue'][$keys[0]] );
			return array( $keys2[0], $result['query-continue'][$keys[0]][$keys2[0]] );
		} else {
			return null;
		}
	}

	/**
	 * Do login
	 * @param string $user user name
	 * @param string $password user password
	 * @return bool if the user is login or not
	 * @throws Exception
	 */
	public function login( $user, $password ) {
		$params = array(
			'action' => 'login'
		);
		$post = array(
			'lgname' => $user,
			'lgpassword' => $password
		);
		$result = $this->post( $params, $post );
		if( $result['login']['result'] === 'NeedToken' ) {
				$post['lgtoken'] = $result['login']['token'];
				$result = $this->post( $params, $post );
		}
		if( $result['login']['result'] === 'Success' ) {
			$this->editToken = null;
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Do logout
	 * @return bool if the user is logout or not
	 * @throws Exception
	 */
	public function logout() {
		$params = array(
			'action' => 'logout'
		);
		$this->get( $params );
		$this->editToken = null;
		return true;
	}

	/**
	 * Returns the edit token for the current user.
	 * @return string
	 **/
	public function getEditToken() {
		if( $this->editToken === null ) {
			$params = array(
				'action' => 'query',
				'prop' => 'info',
				'intoken' => 'edit',
				'titles' => 'Main Page'
			);
			$result = $this->get( $params );
			foreach( $result['query']['pages'] as $ret ) {
				return $ret['edittoken'];
			}
		}
		return $this->editToken;
	}
}
