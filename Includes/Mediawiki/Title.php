<?php

namespace Addframe\Mediawiki;

/**
 * This class is designed to represent a Page Title
 * @author Addshore
 * @since 0.0.4
 **/
class Title {

	/**
	 * @var string of the actual title
	 */
	protected $title;
	/**
	 * @var Page for the title
	 */
	public $page;

	/**
	 * @var string of the namespace id
	 */
	protected $nsid;

	function __construct( $title, $page ) {
		$this->title = $title;
		$this->page = $page;
	}

	/**
	 * @return string of the actual title (including namespace)
	 */
	public function getTitle() {
		return $this->title;
	}

	/**
	 * @return string The title with the namespace removed if possible
	 */
	public function getTitleWithoutNamespace() {
		$this->getNamespaceId();

		if ( $this->nsid != null && $this->nsid != '0' ) {
			$explode = explode( ':', $this->title, '2' );
			return $explode[1];
		}
		return $this->title;
	}

	/**
	 * @return string of the namespaces id
	 */
	public function getNamespaceId() {
		if( $this->nsid == null ){
			$this->nsid = $this->page->site->getNamespaceIdFromTitle( $this->title );
		}
		return $this->nsid;
	}

	/**
	 * @return string Normalise the namespace of the title if possible.
	 */
	public function normaliseTitle() {
		$this->getNamespaceId();

		if ( $this->nsid != '0' ) {
			$siteNamespaces = $this->page->site->requestNamespaces();
			$normalisedNamespace = $siteNamespaces[$this->nsid][0];

			$explosion = explode( ':', $this->title, 2 );
			$explosion[0] = $normalisedNamespace;
			$this->title = implode( ':', $explosion );
		}

		$this->title = str_replace('_', ' ', $this->title);

		return $this->title;
	}

}