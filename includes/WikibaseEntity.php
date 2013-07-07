<?php

/**
 * This class is designed to represent a Mediawiki Wikibase entity
 * @author Addshore
 **/
class WikibaseEntity extends Page{

	public $handel;
	public $id;
	public $value;
	public $lastrevid;
	public $type;
	public $parts;


	function __construct( $handel, $id ) {
		$this->id = $id;//@todo validate the id
		$this->handel = $handel;
	}

	/**
	 * Get the entity from the api
	 * @return array of entity parts
	 */
	function getEntity(){
		$param['action'] = 'wbgetentities';
		$param['ids'] = $this->id;
		$result = Globals::$Sites->getSite($this->handel)->api->doRequest($param);
		foreach($result->value['entities'] as $x){
			$this->pageid = $x['pageid'];
			$this->ns = $x['ns'];
			$this->title = $x['title'];
			$this->lastrevid = $x['lastrevid'];
			$this->timestamp = $x['modified'];
			$this->type = $x['type'];
			if ( isset( $x['labels'] ) ) {
				$this->parts['labels'] = $x['labels'];
			}
			if ( isset( $x['descriptions'] ) ) {
				$this->parts['descriptions'] = $x['descriptions'];
			}
			if ( isset( $x['aliases'] ) ) {
				$this->parts['aliases'] = $x['aliases'];
			}
			if ( isset( $x['claims'] ) ) {
				$this->parts['claims'] = $x['claims'];
			}
			if ( isset( $x['sitelinks'] ) ) {
				$this->parts['sitelinks'] = $x['sitelinks'];
			}
			return $this->parts;
		}
	}


	/**
	 * Builds an entity out of the parts specified
	 * @return array
	 */
	function buildEntity(){
		$parts = array();
		foreach ($this->parts as $key => $part){
			if($part != null){
				$parts[$key] = $part;
			}
		}
		return $parts;
	}

}