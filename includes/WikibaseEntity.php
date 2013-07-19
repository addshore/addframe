<?php

/**
 * This class is designed to represent a Mediawiki Wikibase entity
 * @author Addshore
 **/
class WikibaseEntity extends Page{

	/**
	 * @var Mediawiki
	 */
	public $site;
	public $id;
	public $new = null;
	public $lastrevid;
	public $entityType;
	public $languageData;

	//@todo we should keep a changed status, if we call save without changing just dont bother..?

	function __construct( $site, $id = null , $new = null) {
		if( isset ( $id ) ){
			$this->id = $id;//@todo validate  and correct the id (lower case)
		}
		if( isset( $new ) ){
			$this->new = true;
			$this->entityType = $new;
		}
		$this->site = $site;
	}

	//@todo this should use the stored side db name rather than being passed one
	function getIdFromPage ($site,$title){
		$param['sites'] = $site;
		$param['titles'] = $title;
		$param['props'] = 'info';
		$result = $this->site->doWbGetEntities($param);
		if(!isset($result['entities'])){return false;}
		foreach($result['entities'] as $entity){
			$this->id = $entity['id'];
		}
		return $this->id;
	}


	/**
	 * Get the entity from the api
	 * @return array of entity languageData
	 */
	function load(){
		if( $this->new != true){
			$param['ids'] = $this->id;
			$result = $this->site->doWbGetEntities($param);
			foreach($result['entities'] as $x){
				$this->pageid = $x['pageid'];
				$this->nsid = $x['ns'];
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
			}
			return $this->languageData;
		}
		return null;
	}

	/**
	 * Get the entity from the api
	 */
	function save($summary = null, $minor = null){
		if( !isset($this->id) ){
			$param['new'] = $this->entityType;
		} else {
			$param['id'] = $this->id;
		}
		$param['data'] = $this->serializaData();
		if(isset($summary)){ $param['summary'] = $summary;}
		echo "Saved entity ".$this->id."\n";
		return $this->site->doWbEditEntity($param);
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
		$idkey1 = 'language'; //default
		$idkey2 = 'value';
		if( $type == 'sitelinks' ){ $idkey1 = 'site'; $idkey2 = 'title'; }
		$this->languageData[$type][$identifier][$idkey1] = $identifier;
		$this->languageData[$type][$identifier][$idkey2] = $value;
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
		$this->languageData['aliases'][$language]['language'] = $language;
		$this->languageData['aliases'][$language]['value'] = $value;
	}

	function addAliases($language, $value){
		if( !isset($this->languageData['aliases'][$language]) ){
			$this->modifyAliases($language, $value);
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
			//MSG: no aliases to remove for this language...
		}
	}

	//@todo statements

}