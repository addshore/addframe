<?php

namespace Addframe\Mediawiki\Api;

/**
 * Class LogoutRequest action=logout
 */
class LogoutRequest extends Request{

	public function __construct( $params = array(), $shouldPost = false, $maxAge = CACHE_NONE  ) {

		$this->addAllowedParams( array( 'action' ) );
		$this->addParams( array( 'action' => 'logout' ) );

		parent::__construct( $params, $shouldPost, $maxAge );
	}
}