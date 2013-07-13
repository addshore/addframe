<?php

/**
 * This class is designed to represent a Mediawiki Wikibase entity
 * @author Addshore
 **/
class WikibaseEntity {

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
			$this->entityType = $x['type'];
			//@todo this list of returns should probably be somewhere else
			$canGet = Array('labels', 'descriptions', 'aliases', 'sitelinks');
			foreach ( $canGet as $returnType){
				if ( isset( $x[$returnType]) ) {
					$this->languageData[$returnType] = $x[$returnType];
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
		$param['data'] = $this->serializaData();
		$result = Globals::$Sites->getSite($this->siteHandel)->api->doWbEditEntity($param);
		print_r($result);
		return null;
	}

	/**
	 * Builds an entity out of the languageData specified
	 * @return string of json encoded languageData
	 */
	function serializaData(){
		//@todo remove empty languageData and normalise stuff
		return json_encode($this->languageData);
	}

	/**
	 * @param $type string type of language data to modify
	 * @param $identifier string of the data such as language or site
	 * @param $value string value to set the data to to the identifier
	 */
	function modifyLanguageData($type, $identifier, $value){
		$idkey = 'language'; //default
		if( $type == 'sitelinks' ){ $idkey = 'sites'; }
		$this->languageData[$type][$identifier][$idkey] = $identifier;
		$this->languageData[$type][$identifier]['value'] = $value;
	}

	function addLanguageData($type, $identifier, $value){
		if( !isset($this->languageData[$type][$identifier]) ){
			$this->modifyLanguageData($type, $identifier, $value);
		}
	}

	function removeLanguageData($type, $identifier){
		if( isset($this->languageData[$type][$identifier]) ){
			unset($this->languageData[$type][$identifier]);
		}
	}

	function modifyLabel($language, $value){ $this->modifyLanguageData('labels',$language,$value); }
	function addLabel($language, $value){ $this->addLanguageData('labels', $language, $value); }
	function removeLabel($language){ $this->removeLanguageData('labels',$language); }
	function modifyDescription($language, $value){ $this->modifyLanguageData('descriptions',$language,$value); }
	function addDescription($language, $value){ $this->addLanguageData('descriptions', $language, $value); }
	function removeDescription($language){ $this->removeLanguageData('descriptions',$language); }
	function modifySitelink($siteid, $value){ $this->modifyLanguageData('sitelinks',$siteid,$value); }
	function addSitelink($siteid, $value){ $this->addLanguageData('sitelinks', $siteid, $value); }
	function removeSitelink($siteid){ $this->removeLanguageData('sitelinks',$siteid); }

	function modifyAliases($language, $value){
		$this->languageData['aliases'][$language]['site'] = $language;
		$this->languageData['aliases'][$language]['value'] = $value;
	}

	function addAliases($language, $value){
		if( !isset($this->languageData['aliases'][$language]) ){
			$this->modifySitelink($language, $value);
		}
	}

	function removeAliases($language){
		if( isset($this->languageData['aliases'][$language]) ){
			unset($this->languageData['aliases'][$language]);
		}
	}

	function addAlias($language, $string){
		$this->languageData['aliases'][$language][] = Array('language' => $language, 'value' => $string);
	}
	function removeAlias($language, $string){
		if( isset($this->languageData['aliases'][$language]) ){
			foreach($this->languageData['aliases'][$language] as $key => $alias){
				if( $alias['value'] == $string ){
					unset($this->languageData['aliases'][$language][$key]);
					$this->languageData['aliases'][$language] = array_values( $this->languageData['aliases'][$language] );
				}
			}
		}
		else{
			//NOTICE: no aliases to remove for this language...
		}
	}

	//@todo statements

}