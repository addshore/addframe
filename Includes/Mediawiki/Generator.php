<?php

namespace Addframe\Mediawiki;


class Generator implements \Iterator {

	/**
	 * Site to run the query on
	 * @var Site
	 */
	protected $site;

	/**
	 * Type of generator...not sure if needed
	 * @var string
	 */
	protected $params;

	/**
	 * Current result
	 * @var array
	 */
	protected $res;

	/**
	 * What object we're currently on
	 * @var int
	 */
	protected $pointer = 0;

	/**
	 * Value of the 'continue' param
	 * @var array
	 */
	protected $continue;

	/**
	 * Whether we've fetched all the results
	 * @var bool
	 */
	protected $done = false;

	/**
	 * @param $site Site
	 * @param $params array
	 */
	function __construct( $site, $params ) {
		$this->site = $site;
		$this->params = $params;
	}

	/**
	 * Name of the key inside 'query'
	 * @return string
	 */
	function getName() {
		return $this->params['list'];
	}

	function fetch() {
		if ( $this->done ) {
			return; // We're done.
		}
		$this->params['action'] = 'query'; // Just cuz.
		if ( $this->continue !== null ) {
			$this->params['continue'] = $this->continue;
		}
		$data = $this->site->doRequest( $this->params );
		if ( !isset( $data['continue'] ) && $this->continue ) {
			// We're out of results.
			$this->done = true;
			$this->continue = null;
		}
		$this->continue = $data['continue'];
		$res = $data['query'][$this->getName()];
		$this->res += $res; // Append it
	}

	function current() {
		if ( count( $this->res ) <= $this->pointer ) {
			// Need to fetch moar results!
			$this->fetch();
		}
		return $this->res[$this->pointer];
	}

	function next() {
		$this->pointer++;
	}

	function rewind() {
		$this->pointer = 0;
	}

	function key() {
		return $this->pointer;
	}

	function valid() {
		return $this->current() !== null;
	}
}