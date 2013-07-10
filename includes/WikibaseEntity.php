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
			$canGet = Array('labels', 'descriptions', 'aliases', 'sitelinks');
			foreach ( $canGet as $returnType){
				if ( isset( $x[$returnType]) ) {
					$this->parts[$returnType] = $x[$returnType];
				}
			}
			return $this->parts;
		}
	}

	/**
	 * Get the entity from the api
	 */
	function saveEntity(){
		//@todo some of this should probably go in the api...
		$param['action'] = 'wbeditentity';
		$param['id'] = $this->id;
		$post['data'] = $this->buildEntity();
		$result = Globals::$Sites->getSite($this->handel)->api->doRequest($param,$post);
		//@todo this should return a status
		print_r($result);
		return null;
	}

	/**
	 * Builds an entity out of the parts specified
	 * @return array
	 */
	function buildEntity(){
		return json_encode($this->parts);
	}

	function modifyLabel($language, $label){
		$this->parts['labels'][$language]['language'] = $language;
		$this->parts['labels'][$language]['value'] = $label;
	}

}