<?php

/**
 * This class is designed to represet a mediawiki page
 * @author Addshore
 **/
class page {

	public $title;

	/**
	 * @param $title string Title of page
	 */
	function __construct( $title ) {
		$this->title = $title;
	}

}