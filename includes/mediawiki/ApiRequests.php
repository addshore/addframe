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
	function __construct(
		$title = null,
		$pageid = null,
		$section = null,
		$sectiontitle = null,
		$text = null,
		$token = null,
		$summary = null,
		$minor = null,
		$bot = null,
		$basetimestamp = null,
		$starttimestamp = null,
		$recreate = null,
		$createonly = null,
		$nocreate = null,
		$watchlist = null,
		$prependtext = null,
		$appendtext = null,
		$undo = null,
		$undoafter = null,
		$redirect = null,
		$contentformat = null,
		$contentmodel = null
	) {
		$action = 'edit';
		if( !is_null( $text ) ){
			$md5 = md5( $text );
		} else if( !is_null( $prependtext ) && !is_null( $appendtext ) ) {
			$md5 = md5( $prependtext . $appendtext );
		}
		parent::__construct( get_defined_vars(), true, CACHE_NONE );
	}
}