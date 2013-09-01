<?php

namespace Addframe\Mediawiki\Api;

/**
 * Class UserinfoRequest
 */
class UserinfoRequest extends QueryRequest{

	function __construct( $params = array(), $shouldBePosted = false, $maxAge = CACHE_DAY ) {

		$this->addAllowedParams( array( 'meta', 'uiprop' ) );
		$this->addParams( array( 'meta' => 'userinfo' ) );

		parent::__construct( $params, $shouldBePosted, $maxAge);
	}

}