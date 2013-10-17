<?php

namespace Addframe\Mediawiki\Api;

/**
 * Class TokensRequest action=tokens
 */
class TokensRequest extends Request{

	public function __construct( $params = array (), $shouldPost = false ) {

		$this->addAllowedParams( array( 'action', 'type' ) );
		$this->addParams( array( 'action' => 'tokens', 'type' => 'edit' ) );

		parent::__construct( $params, $shouldPost );
	}
}