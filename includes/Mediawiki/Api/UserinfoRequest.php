<?php

namespace Addframe\Mediawiki\Api;

/**
 * Class UserinfoRequest
 */
class UserinfoRequest extends QueryRequest{

	public function __construct( $params = array (), $shouldPost = false ) {

		$this->addAllowedParams( array( 'meta', 'uiprop' ) );
		$this->addParams( array( 'meta' => 'userinfo' ) );

		parent::__construct( $params, $shouldPost );
	}

}