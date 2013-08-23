<?php

namespace Addframe\Mediawiki;

use Addframe\Mediawiki\ApiRequest;

/**
 * This file contains classes for all api functions in the core of mediawiki
 */

class QueryRequest extends ApiRequest{
	function __construct( $params = array(), $shouldBePosted = false, $cache = CACHE_NONE ){
		parent::__construct( array_merge( array( 'action' => 'query' ), $params ), $shouldBePosted, $cache );
	}
}

class SiteInfoRequest extends QueryRequest{
	function __construct(
		$siprop = 'general',
		$sifilteriw = null,
		$sishowalldb = null,
		$sinumberingroup = null,
		$siinlanguagecode = null
	) {
		parent::__construct( array(
			'meta' => 'siteinfo',
			'siprop' => $siprop,
			'sifilteriw' => $sifilteriw,
			'sishowalldb' => $sishowalldb,
			'sinumberingroup' => $sinumberingroup,
			'siinlanguagecode' => $siinlanguagecode,
		), false, CACHE_WEEK );
	}
}

class TokensRequest extends ApiRequest{
	function __construct( $type = 'edit' ) {
		parent::__construct( array(
			'action' => 'tokens',
			'type' => $type,
		), false, CACHE_NONE );
	}
}