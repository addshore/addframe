<?php

namespace Addframe\Mediawiki\Api;

/**
 * Class QueryRequest action=query
 */
class QueryRequest extends Request{

	function __construct( $params = array(), $shouldBePosted = false, $maxAge = CACHE_NONE ){

		$this->addAllowedParams(
			array( 'action', 'prop', 'list', 'meta', 'indexpageids', 'export', 'exportnowrap', 'iwurl', 'continue',
				'titles', 'pageids', 'revids', 'redirects', 'converttitles', 'generator' ) );

		$this->addParams( array( 'action' => 'query' ) );

		parent::__construct( $params, $shouldBePosted, $maxAge );
	}
}