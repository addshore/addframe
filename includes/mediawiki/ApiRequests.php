<?php

namespace Addframe\Mediawiki;

use Addframe\Mediawiki\ApiRequest;

/**
 * This file contains classes for all api functions in the core of mediawiki
 */

/**
 * Class QueryRequest action=query
 */
class QueryRequest extends ApiRequest{
	function __construct( $params = array(), $shouldBePosted = false, $maxAge = CACHE_NONE ){
		parent::__construct( array_merge( array( 'action' => 'query' ), $params ), $shouldBePosted, $maxAge );
	}
}

/**
 * Class SiteInfoRequest meta=siteinfo
 */
class SiteInfoRequest extends QueryRequest{
	function __construct(
		$siprop = 'general',
		$sifilteriw = null,
		$sishowalldb = null,
		$sinumberingroup = null,
		$siinlanguagecode = null
	) {
		$meta = 'siteinfo';
		parent::__construct( get_defined_vars(), false, CACHE_WEEK );
	}
}

/**
 * Class LoginRequest action=login
 */
class LoginRequest extends ApiRequest{
	function __construct( $lgname = null, $lgpassword = null, $lgtoken = null, $lgdomain = null ) {
		$action = 'login';
		parent::__construct( get_defined_vars(), true, CACHE_NONE );
	}
}

/**
 * Class LogoutRequest action=logout
 */
class LogoutRequest extends ApiRequest{
	function __construct( ) {
		parent::__construct( array( 'action' => 'logout' ), false, CACHE_NONE );
	}
}

/**
 * Class TokensRequest action=tokens
 */
class TokensRequest extends ApiRequest{
	function __construct( $type = 'edit' ) {
		$action = 'tokens';
		parent::__construct( get_defined_vars(), false, CACHE_HOUR );
	}
}
/**
 * Class EditRequest action=edit
 */
class EditRequest extends ApiRequest{
	function __construct( $params = array() ) {
		$params['action'] = 'edit';
		if( array_key_exists( 'text', $params ) && !is_null( $params['text'] ) ){
			$params['md5'] = md5( $params['text'] );
		} else if( array_key_exists( 'prependtext', $params ) && array_key_exists( 'appendtext', $params )
			&& !is_null( $params['prependtext'] ) && !is_null( $params['appendtext'] ) ) {
			$params['md5'] = md5( $params['prependtext'] . $params['appendtext'] );
		}
		parent::__construct( $params, true, CACHE_NONE );
	}
}