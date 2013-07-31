<?php

namespace Addframe;

/**
 * This class is designed to represent a Site Wikibase entity
 * @author Addshore
 **/
class Entity extends Page {

	/** @var Site site of entity */
	public $site;
	/** @var string id of entity */
	public $id;
	/** @var boolean is this a new entity? Does it need to be created? */
	public $new = null;
	public $lastrevid;
	/** @var string type of entity (property|item) */
	public $entityType;
	/** @var array languagedata for the item (alliases|labels|sitelinks) */
	public $languageData;
	/** @var boolean has the item been changed since it was loaded? */
	public $changed = false;


	//@todo manipulate statements

	function __construct( $site, $id = null, $new = null ) {
		if ( isset ( $id ) ) {
			//todo this is a valid id
			$this->id = strtolower( $id );
		}
		if ( isset( $new ) ) {
			$this->new = true;
			$this->entityType = $new;
		}
		$this->site = $site;
	}

	/**
	 * @param $site string of page
	 * @param $title string of page
	 * @return bool|string false or the id
	 */
	function getIdFromPage( $site, $title ) {
		$param['sites'] = $site;
		$param['titles'] = $title;
		$param['props'] = 'info';
		$result = $this->site->requestWbGetEntities( $param );
		if ( ! isset( $result['entities'] ) ) {
			return false;
		}
		foreach ( $result['entities'] as $entity ) {
			$this->id = $entity['id'];
		}
		return $this->id;
	}


	/**
	 * Get the entity from the api
	 * @return array of entity languageData
	 */
	function load() {
		//@todo refactor into site->getentitydatafromid
		if ( $this->new != true ) {
			$param['ids'] = $this->id;
			$result = $this->site->requestWbGetEntities( $param );
			foreach ( $result['entities'] as $x ) {
				$this->pageid = $x['pageid'];
				$this->nsid = $x['ns'];
				$this->title = $x['title'];
				$this->lastrevid = $x['lastrevid'];
				$this->entityType = $x['type'];
				$this->languageData = $this->unserializeLanguageData( $x );
			}
			return $this->languageData;
		}
		return null;
	}

	/**
	 * Save the entity through the api
	 */
	function save( $summary = null, $minor = null ) {
		if ( ! isset( $this->id ) ) {
			$param['new'] = $this->entityType;
		} else {
			$param['id'] = $this->id;
		}
		$param['data'] = $this->serializeLanguageData();
		if ( $param['data'] == json_encode( array() ) ) {
			$param['clear'] = 'true';
		}
		if ( isset( $summary ) ) {
			$param['summary'] = $summary;
		}
		if( isset( $this->lastrevid ) ){
			$param['baserevid'] = $this->lastrevid;
		}
		$param['bot'] = '';
		
		echo "Saving entity " . $this->id . "\n";
		$result = $this->site->requestWbEditEntity( $param );
		return $result;
	}

	/**
	 * Builds an entity out of the languageData specified
	 * @return string of json encoded languageData
	 */
	function serializeLanguageData() {
		foreach ( $this->languageData as $key => $data ) {
			if ( $data === array() ) {
				unset( $this->languageData[$key] );
			}
		}

		//@todo normalise stuff before returning
		return json_encode( $this->languageData );
	}

	/**
	 * unserializes the data returned from the api into an array we can use
	 * @param $json string of json data which includes language data
	 * @return array of LanguageData
	 */
	function unserializeLanguageData( $json ) {
		$canGet = Array( 'labels', 'descriptions', 'aliases', 'sitelinks' );
		foreach ( $canGet as $returnType ) {
			if ( isset( $json[$returnType] ) ) {
				$this->languageData[$returnType] = $json[$returnType];
			}
		}
		return $this->languageData;
	}

	/**
	 * @param $type string type of language data to modify
	 * @param $identifier string of the data such as language or site
	 * @param $value string value to set the data to to the identifier
	 */
	function modifyLanguageData( $type, $identifier, $value ) {
		$idkey1 = 'language'; //default
		$idkey2 = 'value';
		if ( $type == 'sitelinks' ) {
			$idkey1 = 'site';
			$idkey2 = 'title';
		}
		$this->languageData[$type][$identifier][$idkey1] = $identifier;
		$this->languageData[$type][$identifier][$idkey2] = $value;
		$this->changed = true;
	}

	function addLanguageData( $type, $identifier, $value ) {
		if ( ! isset( $this->languageData[$type][$identifier] ) ) {
			$this->modifyLanguageData( $type, $identifier, $value );
		}
	}

	function removeLanguageData( $type, $identifier ) {
		if ( isset( $this->languageData[$type][$identifier] ) ) {
			unset( $this->languageData[$type][$identifier] );
			$this->changed = true;
		}
	}

	function modifyLabel( $language, $value ) {
		$this->modifyLanguageData( 'labels', $language, $value );
	}

	function addLabel( $language, $value ) {
		$this->addLanguageData( 'labels', $language, $value );
	}

	function removeLabel( $language ) {
		$this->removeLanguageData( 'labels', $language );
	}

	function modifyDescription( $language, $value ) {
		$this->modifyLanguageData( 'descriptions', $language, $value );
	}

	function addDescription( $language, $value ) {
		$this->addLanguageData( 'descriptions', $language, $value );
	}

	function removeDescription( $language ) {
		$this->removeLanguageData( 'descriptions', $language );
	}

	function modifySitelink( $siteid, $value ) {
		$this->modifyLanguageData( 'sitelinks', $siteid, $value );
	}

	function addSitelink( $siteid, $value ) {
		$this->addLanguageData( 'sitelinks', $siteid, $value );
	}

	function removeSitelink( $siteid ) {
		$this->removeLanguageData( 'sitelinks', $siteid );
	}

	function modifyAliases( $language, $value ) {
		$this->languageData['aliases'][$language]['language'] = $language;
		$this->languageData['aliases'][$language]['value'] = $value;
		$this->changed = true;
	}

	function addAliases( $language, $value ) {
		if ( ! isset( $this->languageData['aliases'][$language] ) ) {
			$this->modifyAliases( $language, $value );
		}
	}

	function removeAliases( $language ) {
		if ( isset( $this->languageData['aliases'][$language] ) ) {
			unset( $this->languageData['aliases'][$language] );
			$this->changed = true;
		}
	}

	function addAlias( $language, $string ) {
		$this->languageData['aliases'][$language][] = Array( 'language' => $language, 'value' => $string );
		$this->changed = true;
	}

	function removeAlias( $language, $string ) {
		if ( isset( $this->languageData['aliases'][$language] ) ) {
			foreach ( $this->languageData['aliases'][$language] as $key => $alias ) {
				if ( $alias['value'] == $string ) {
					unset( $this->languageData['aliases'][$language][$key] );
					$this->changed = true;
					$this->languageData['aliases'][$language] = array_values( $this->languageData['aliases'][$language] );
				}
			}
		} else {
			echo "No alises to remove for language '$language'\n";
		}
	}

	/**
	 * @param string $property id of property to filter
	 * @return mixed
	 */
	public function getClaims( $property = nul ) {
		$params['entity'] = $this->id;
 		if( isset( $property ) ){
			$params['property'] = $property;
		}
		$result = $this->site->requestWbGetClaims( $params );
		return $result['claims'];
	}

	/**
	 * @param $snaktype string One value: value, novalue, somevalue
	 * @param $property string id of property
	 * @param $value string json value
	 * @return mixed
	 */
	public function createClaim( $snaktype, $property, $value ) {
		$params['entity'] = $this->id;
		$params['snaktype'] = $snaktype;
		if(isset($property)){
			$params['property'] = $property;
		}
		if(isset($value)){
			$params['value'] = $value;
		}

		$result = $this->site->requestWbCreateClaim( $params );
		return $result;
	}

}