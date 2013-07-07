<?php

/**
 * This class is designed to represent a Mediawiki Wikibase entity
 * @author Addshore
 **/
class WikibaseEntity extends Page{

	public $handel;
	public $id;
	public $value;


	function __construct( $handel, $id ) {
		$this->id = $id;//@todo validate the id
		$this->handel = $handel;
	}

	function getEntity(){
		$param['action'] = 'wbgetentities';
		$param['ids'] = $this->id;
		$result = Globals::$Sites->getSite($this->handel)->api->doRequest($param);
		$this->value = $result->value['entities'][$param['ids']];
		//@todo this needs to remove the crap and instead add the crap do didfferent vars
		print_r($result);
	}

}