<?php

namespace Addframe\Mediawiki\Api;

/**
 * Class UsersRequest
 */
class UsersRequest extends QueryRequest{

	public function __construct( $params = array (), $shouldPost = false ) {

		$this->addAllowedParams( array( 'list', 'usprop', 'ususers', 'ustoken' ) );
		$this->addParams( array(
			'list' => 'users',
			'usprop' => 'blockinfo|groups|implicitgroups|rights|editcount|registration|emailable|gender'
		) );

		parent::__construct( $params, $shouldPost );
	}

}