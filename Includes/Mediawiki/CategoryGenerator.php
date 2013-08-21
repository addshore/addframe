<?php

namespace Addframe\Mediawiki;


class CategoryGenerator extends Generator {

	/**
	 * @param Site $site
	 * @param Page $category
	 */
	public function __construct( $site, $category ) {
		$params = array(
			'list' => 'categorymembers',
			'cmtitle' => $category->getTitle(),
			'cmlimit' => 'max',
		);
		parent::__construct( $site, $params );
	}

	public function current() {
		$data = parent::current();
		return Page::newFromGenerator( $this->site, $data );
	}
}
