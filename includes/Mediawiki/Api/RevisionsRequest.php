<?php

namespace Addframe\Mediawiki\Api;

/**
 * Class RevisionsRequest
 */
class RevisionsRequest extends QueryRequest{

	function __construct( $params = array(), $shouldBePosted = true, $maxAge = CACHE_NONE ) {

		$this->addAllowedParams( array( 'prop', 'rvprop', 'rvlimit', 'rvstartid', 'rvendid',
			'rvstarts', 'rvend', 'rvdir', 'rvuser', 'rvexcludeuser', 'rvtag', 'rvexpandtemplates',
			'rvgeneratexml', 'rvparse', 'rvsection', 'rvtoken', 'rvcontinue', 'rvdiffto',
			'rvdifftotext', 'rvcontentformat' ) );

		$this->addParams( array( 'prop' => 'revisions' ) );

		parent::__construct( $params, $shouldBePosted, $maxAge);
	}

}