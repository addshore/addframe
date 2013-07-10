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

	//@todo we should keep a changed status, if we call save without changing just dont bother..?

	function __construct( $handel, $id ) {
		$this->id = $id;//@todo validate  and correct the id (lower case)
		$this->handel = $handel;
	}

	/**
	 * Get the entity from the api
	 * @return array of entity parts
	 */
	function getEntity(){
		$param['ids'] = $this->id;
		$result = Globals::$Sites->getSite($this->handel)->api->doWbGetEntities($param);
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
		$param['id'] = $this->id;
		$param['data'] = $this->buildEntity();
		$result = Globals::$Sites->getSite($this->handel)->api->doWbEditEntity($param);
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

	//add label if it doesn't already exist
	function addLabel($language, $label){
		if( !isset($this->parts['labels'][$language]) ){
			$this->modifyLabel($label, $label);
		}
	}

	//Modify the label (this will over write if it already exists)
	function modifyLabel($language, $label){
		$this->parts['labels'][$language]['language'] = $language;
		$this->parts['labels'][$language]['value'] = $label;
	}

}