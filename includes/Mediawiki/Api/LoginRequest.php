<?php

namespace Addframe\Mediawiki\Api;

/**
 * Class LoginRequest action=login
 */
class LoginRequest extends Request{

	function __construct( $params = array(), $shouldBePosted = true, $maxAge = CACHE_NONE ) {

		$this->addAllowedParams( array( 'action', 'lgname', 'lgpassword', 'lgtoken', 'lgdomain' ) );
		$this->addParams( array( 'action' => 'login' ) );

		parent::__construct( $params, $shouldBePosted, $maxAge);
	}
}