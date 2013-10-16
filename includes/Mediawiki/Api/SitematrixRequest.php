<?php

namespace Addframe\Mediawiki\Api;

class SitematrixRequest extends Request {

	public function __construct( $params = array(), $shouldPost = true, $maxAge = CACHE_WEEK ) {

		$this->addAllowedParams( array( 'action', 'smtype', 'smstate', 'smlangprop', 'smsiteprop', 'smlimit', 'smcontinue' ) );
		$this->addParams( array( 'action' => 'sitematrix' ) );

		parent::__construct( $params, $shouldPost, $maxAge);
	}
}