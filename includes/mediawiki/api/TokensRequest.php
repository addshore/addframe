<?php

namespace Addframe\Mediawiki\Api;

/**
 * Class TokensRequest action=tokens
 */
class TokensRequest extends Request{

	function __construct( $params = array(), $shouldBePosted = false, $maxAge = CACHE_HOUR ) {

		$this->addAllowedParams( array( 'action', 'type' ) );
		$this->addParams( array( 'action' => 'tokens', 'type' => 'edit' ) );

		parent::__construct( $params, $shouldBePosted, $maxAge );
	}
}