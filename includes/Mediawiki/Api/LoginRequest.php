<?php

namespace Addframe\Mediawiki\Api;

/**
 * Class LoginRequest action=login
 */
class LoginRequest extends Request{

	public function __construct( $params = array (), $shouldPost = true ) {

		$this->addAllowedParams( array( 'action', 'lgname', 'lgpassword', 'lgtoken', 'lgdomain' ) );
		$this->addParams( array( 'action' => 'login' ) );

		parent::__construct( $params, $shouldPost );
	}
}