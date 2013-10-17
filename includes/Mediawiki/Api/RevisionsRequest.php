<?php

namespace Addframe\Mediawiki\Api;

/**
 * Class RevisionsRequest
 */
class RevisionsRequest extends QueryRequest{

	public function __construct( $params = array (), $shouldPost = true ) {

		$this->addAllowedParams( array( 'prop', 'rvprop', 'rvlimit', 'rvstartid', 'rvendid',
			'rvstarts', 'rvend', 'rvdir', 'rvuser', 'rvexcludeuser', 'rvtag', 'rvexpandtemplates',
			'rvgeneratexml', 'rvparse', 'rvsection', 'rvtoken', 'rvcontinue', 'rvdiffto',
			'rvdifftotext', 'rvcontentformat' ) );

		$this->addParams( array( 'prop' => 'revisions' ) );
		$this->addParams( array( 'rvprop' => 'ids|flags|timestamp|user|userid|size|sha1|contentmodel|comment|parsedcomment|content|tags' ) );

		parent::__construct( $params, $shouldPost );
	}

}