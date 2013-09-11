<?php

namespace Addframe\Mediawiki\Api;

/**
 * Class SiteinfoRequest meta=siteinfo
 */
class SiteinfoRequest extends QueryRequest{

	function __construct( $params = array(), $shouldBePosted = false, $maxAge = CACHE_WEEK ) {

		$this->addAllowedParams( array( 'meta', 'siprop', 'sifilteriw', 'sishowalldb', 'sinumberingroup', 'siinlanguagecode' ) );
		$this->addParams( array( 'meta' => 'siteinfo' ) );

		parent::__construct( $params, $shouldBePosted, $maxAge );
	}
}