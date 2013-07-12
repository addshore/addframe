<?php

/**
 * This class is designed to represent a Mediawiki Wikibase entity
 * @author Addshore
 **/
class WikibaseEntity extends Page{

	public $siteHandel;
	public $id;
	public $lastrevid;
	public $entityType;
	public $languageData;

	//@todo we should keep a changed status, if we call save without changing just dont bother..?

	function __construct( $siteHandel, $id ) {
		$this->id = $id;//@todo validate  and correct the id (lower case)
		$this->siteHandel = $siteHandel;
	}

	/**
	 * Get the entity from the api
	 * @return array of entity languageData
	 */
	function loadEntity(){
		$param['ids'] = $this->id;
		$result = Globals::$Sites->getSite($this->siteHandel)->api->doWbGetEntities($param);
		foreach($result->value['entities'] as $x){
			$this->pageid = $x['pageid'];
			$this->ns = $x['ns'];
			$this->title = $x['title'];
			$this->lastrevid = $x['lastrevid'];
			$this->timestamp = $x['modified'];
			$this->entityType = $x['entityType'];
			//@todo this list of returns should probably be somewhere else
			$canGet = Array('labels', 'descriptions', 'aliases', 'sitelinks');
			foreach ( $canGet as $returnType){
				if ( isset( $x[$returnType]) ) {
					//@todo work out how to handle claims
					if( $returnType == 'aliases' ){
						//Aliases are an array of arrays, so split them up into a simple array
						foreach ($x[$returnType] as $xLanguage => $xArrayList){
							foreach ($xArrayList as $xArray){
								$this->languageData[$returnType][$xLanguage][] = $xArray['value'];
							}
						}
					}else{
						$this->languageData[$returnType] = $x[$returnType];
					}
				}
			}
			return $this->languageData;
		}
	}

	/**
	 * Get the entity from the api
	 */
	function saveEntity(){
		//@todo some of this should probably go in the api...
		$param['id'] = $this->id;
		$param['data'] = $this->buildEntity();
		$result = Globals::$Sites->getSite($this->siteHandel)->api->doWbEditEntity($param);
		print_r($result);
		return null;
	}

	/**
	 * Builds an entity out of the languageData specified
	 * @return string of json encoded languageData
	 */
	function buildEntity(){
		//@todo remove empty languageData and normalise stuff
		return json_encode($this->languageData);
	}

	//@todo below labels and descriptions are effectively the same.. use the same stuff

	//Modify the label (this will over write if it already exists)
	function modifyLabel($language, $value){
		$this->languageData['labels'][$language]['language'] = $language;
		$this->languageData['labels'][$language]['value'] = $value;
	}

	//add label if it doesn't already exist
	function addLabel($language, $value){
		if( !isset($this->languageData['labels'][$language]) ){
			$this->modifyLabel($language, $value);
		}
	}

	//remove the label for the language
	function removeLabel($language){
		if( isset($this->languageData['labels'][$language]) ){
			unset($this->languageData['labels'][$language]);
		}
	}

	//Modify the description (this will over write if it already exists)
	function modifyDescription($language, $value){
		$this->languageData['descriptions'][$language]['language'] = $language;
		$this->languageData['descriptions'][$language]['value'] = $value;
	}

	//add description if it doesn't already exist
	function addDescription($language, $value){
		if( !isset($this->languageData['descriptions'][$language]) ){
			$this->modifyLabel($language, $value);
		}
	}

	//remove the description for the language
	function removeDescription($language){
		if( isset($this->languageData['descriptions'][$language]) ){
			unset($this->languageData['descriptions'][$language]);
		}
	}

	//Modify the sitelink (this will over write if it already exists)
	function modifySitelink($language, $value){
		$this->languageData['sitelinks'][$language]['language'] = $language;
		$this->languageData['sitelinks'][$language]['value'] = $value;
	}

	//add sitelink if it doesn't already exist
	function addSitelink($language, $value){
		if( !isset($this->languageData['sitelinks'][$language]) ){
			$this->modifyLabel($language, $value);
		}
	}

	//remove the sitelink for the language
	function removeSitelink($language){
		if( isset($this->languageData['sitelinks'][$language]) ){
			unset($this->languageData['sitelinks'][$language]);
		}
	}

	//@todo aliases

	//@todo statements

}