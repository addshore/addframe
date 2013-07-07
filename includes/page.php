<?php

/**
 * This class is designed to represet a mediawiki page
 * @author Addshore
 **/
class page {

	/**
	 * @var string handel for associated site
	 */
	public $handel;
	public $title;
	public $text;

	function __construct( $handel , $title ) {
		$this->handel = $handel;
		$this->title = $title;
	}

	function getText(){
		$param['titles'] = $this->title;
		$param['rvprop'] = 'content';
		$result = Globals::$Sites->getSite($this->handel)->api->doPropRevsions($param);
		foreach($result->value['query']['pages'] as $x){
			$this->text = $x['revisions']['0']['*'];
		}
		return $this->text;
	}

}