<?php

namespace Addframe\Mediawiki\Api;

/**
 * Class LogoutRequest action=logout
 */
class LogoutRequest extends Request{

	public function __construct( $params = array (), $shouldPost = false ) {

		$this->addAllowedParams( array( 'action' ) );
		$this->addParams( array( 'action' => 'logout' ) );

		parent::__construct( $params, $shouldPost );
	}
}