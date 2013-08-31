<?php

namespace Addframe\Mediawiki\Api;

class SitematrixRequest extends Request {

	function __construct( $params = array(), $shouldBePosted = true, $maxAge = CACHE_WEEK ) {

		$this->addAllowedParams( array( 'action', 'smtype', 'smstate', 'smlangprop', 'smsiteprop', 'smlimit', 'smcontinue' ) );
		$this->addParams( array( 'action' => 'sitematrix' ) );

		parent::__construct( $params, $shouldBePosted, $maxAge);
	}
}