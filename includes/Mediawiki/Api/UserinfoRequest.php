<?php

namespace Addframe\Mediawiki\Api;

/**
 * Class UserinfoRequest
 * We will never want to cache these as they return information about the currently logged in user
 */
class UserinfoRequest extends QueryRequest{

	public function __construct( $params = array(), $shouldBePosted = false, $maxAge = CACHE_NONE ) {

		$this->addAllowedParams( array( 'meta', 'uiprop' ) );
		$this->addParams( array( 'meta' => 'userinfo' ) );

		parent::__construct( $params, $shouldBePosted, $maxAge);
	}

}