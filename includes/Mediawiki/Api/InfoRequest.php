<?php

namespace Addframe\Mediawiki\Api;

/**
 * Class InfoRequest
 */
class InfoRequest extends QueryRequest{

	public function __construct( $params = array(), $shouldPost = false, $maxAge = CACHE_NONE ) {

		$this->addAllowedParams( array( 'prop', 'inprop', 'intoken', 'incontinue' ) );
		$this->addParams( array( 'prop' => 'info' ) );
		$this->addParams( array( 'inprop' => 'protection|talkid|watched|watchers|notificationtimestamp|subjectid|url|readable|preload|displaytitle' ) );

		parent::__construct( $params, $shouldPost, $maxAge);
	}

}