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
		$this->id = $id;//@todo validate  and correct the id (lower case)
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
			//@todo this list of returns should probably be somewhere else
			$canGet = Array('labels', 'descriptions', 'aliases', 'claims', 'sitelinks');
			foreach ( $canGet as $returnType){
				if ( isset( $x[$returnType]) ) {
					$this->parts[$returnType] = $x[$returnType];
				}
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