<?php

namespace Addframe\Mediawiki;

use Addframe\Mediawiki\ApiRequest;

/**
 * This file contains classes for all api functions in the core of mediawiki
 *
 * These classes should all be derived from ApiRequest
 * These classes should 'only' contain a constructor
 *
 * These constructors should:
 * - specify the default POST and CACHE params
 * - add any api request params it can / should
 */

/**
 * Class QueryRequest action=query
 */
class QueryRequest extends ApiRequest{

	function __construct( $params = array(), $shouldBePosted = false, $maxAge = CACHE_NONE ){
		$this->addParams( array( 'action' => 'query' ) );
		parent::__construct( $params, $shouldBePosted, $maxAge );
	}
}

/**
 * Class SiteInfoRequest meta=siteinfo
 */
class SiteInfoRequest extends QueryRequest{

	function __construct( $params = array(), $shouldBePosted = false, $maxAge = CACHE_WEEK ) {
		$this->addParams( array( 'meta' => 'siteinfo' ) );
		$this->addAllowedParams( array( 'meta', 'siprop', 'sifilteriw', 'sishowalldb', 'sinumberingroup', 'siinlanguagecode' ) );
		parent::__construct( $params, $shouldBePosted, $maxAge );
	}
}

/**
 * Class LoginRequest action=login
 */
class LoginRequest extends ApiRequest{

	function __construct( $params = array(), $shouldBePosted = true, $maxAge = CACHE_NONE ) {
		$this->addParams( array( 'action' => 'login' ) );
		$this->addAllowedParams( array( 'action', 'lgname', 'lgpassword', 'lgtoken', 'lgdomain' ) );
		parent::__construct( $params, $shouldBePosted, $maxAge );
	}
}

/**
 * Class LogoutRequest action=logout
 */
class LogoutRequest extends ApiRequest{

	function __construct( $params = array(), $shouldBePosted = false, $maxAge = CACHE_NONE  ) {
		$this->addParams( array( 'action' => 'logout' ) );
		$this->addAllowedParams( array( 'action' ) );
		parent::__construct( $params, $shouldBePosted, $maxAge );
	}
}

/**
 * Class TokensRequest action=tokens
 */
class TokensRequest extends ApiRequest{

	function __construct( $params = array(), $shouldBePosted = false, $maxAge = CACHE_HOUR ) {
		$this->addParams( array( 'action' => 'tokens', 'type' => 'edit' ) );
		$this->addAllowedParams( array( 'action', 'type' ) );
		parent::__construct( $params, $shouldBePosted, $maxAge );
	}
}
/**
 * Class EditRequest action=edit
 */
class EditRequest extends ApiRequest{

	function __construct( $params = array(), $shouldBePosted = true, $maxAge = CACHE_NONE ) {
		$this->addParams( array( 'action' => 'edit' ) );
		$this->addAllowedParams(
			array( 'action', 'title', 'pageid', 'section', 'sectiontitle', 'text', 'token', 'summary', 'minor',
				'notminor', 'bot', 'basetimestamp', 'starttimestamp', 'recreate', 'createonly', 'nocreate', 'watch',
				'unwatch', 'watchlist', 'md5', 'prependtext', 'appendtext', 'undo', 'undoafter', 'redirect',
				'contentformat', 'contentmodel' ) );

		if( array_key_exists( 'text', $params ) && !is_null( $params['text'] ) ){
			$params['md5'] = md5( $params['text'] );
		/**
		 * todo, see if the below handeling is correct..
		 * The MD5 hash of the text parameter, or the prependtext and appendtext parameters concatenated.
		 * todo this could mean even if one is empty the md5 can be used with the concatenated strings
		 */
		} else if( array_key_exists( 'prependtext', $params ) && array_key_exists( 'appendtext', $params )
			&& !is_null( $params['prependtext'] ) && !is_null( $params['appendtext'] ) ) {
			$params['md5'] = md5( $params['prependtext'] . $params['appendtext'] );
		}
		parent::__construct( $params, $shouldBePosted, $maxAge );
	}
}