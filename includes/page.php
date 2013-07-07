<?php

/**
 * This class is designed to represet a Mediawiki Page
 * @author Addshore
 **/
class Page {

	/**
	 * @var string handel for associated site
	 */
	public $handel;
	/**
	 * @var string title of Page
	 */
	public $title;
	/**
	 * @var string text of Page
	 */
	public $text;
	/**
	 * @var string pageid for Page
	 */
	public $pageid;
	/**
	 * @var string namespace id number eg. 2
	 */
	public $ns;
	/**
	 * @var string timestamp for the particular revision text we have got
	 */
	public $timestamp;

	function __construct( $handel , $title ) {
		$this->handel = $handel;
		$this->title = $title;
	}

	/**
	 * @return string text of Page
	 */
	function getText(){
		$param['titles'] = $this->title;
		$param['rvprop'] = 'content|timestamp';
		$result = Globals::$Sites->getSite($this->handel)->api->doPropRevsions($param);
		foreach($result->value['query']['pages'] as $x){
			$this->pageid = $x['pageid'];
			$this->ns = $x['ns'];
			$this->text = $x['revisions']['0']['*'];
			$this->timestamp = $x['revisions']['0']['timestamp'];
		}
		return $this->text;
	}

	/**
	 * @param null $summary string to save the Page with
	 * @param bool $minor should be minor?
	 */
	function save($summary = null, $minor = false){
		Globals::$Sites->getSite($this->handel)->doEdit($this->title,$this->text,$summary,$minor);
	}

	/**
	 * @param $text string to append to $text
	 */
	function appendText($text){
		if ( ! empty( $this ) ) {
			$this->text = $this->text.$text;
		}
	}

	/**
	 * @param $text string to prepend to $text
	 */
	function prependText($text){
		if ( ! empty( $this ) ) {
			$this->text = $text.$this->text;
		}
	}

	/**
	 * Empties the text of the page
	 */
	function emptyText(){
		$this->text = "";
	}

}