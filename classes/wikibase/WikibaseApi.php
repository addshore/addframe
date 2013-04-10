<?php

/**
 * Base class for the Wikibase API.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 * http://www.gnu.org/copyleft/gpl.html
 *
 * @file
 *
 * @licence GNU GPL v2+
 * @author Thomas Pellissier Tanon
 */
class WikibaseApi extends Api {

	/**
	 * @deprecated The function will return the result array. For getting Entity objects, use EntityProvider::getEntityFromIds
	 * @param string[] $ids the IDs of the entities to get the data from
	 * @param string[] $languages languages for labels/descriptions
	 * @return Entity[]
	 * @throws Exception
	 * @todo Error management
	 */
	public function getEntitiesFromIds( array $ids, array $languages = array() ) {
		$params = array(
			'action' => 'wbgetentities',
			'ids' => implode( $ids, '|' )
		);
		if( $languages !== array() ) {
			$params['languages'] = implode( $languages, '|' );
		}
		$response = $this->get( $params );
		return $this->parseGetEntitiesApiResponse( $response );
	}

	/**
	 * @deprecated use EntityProvider::getEntityFromId
	 * @param string $id the ID of the entities to get the data from
	 * @param string[] $languages languages for labels/descriptions
	 * @return Entity|null
	 * @throws Exception
	 */
	public function getEntityFromId( $id, array $languages = array() ) {
		$entities = $this->getEntitiesFromIds( array( $id ), $languages );
		foreach( $entities as $entity ) {
			return $entity;
		}
		return null;
	}

	/**
	 * @deprecated The function will return the result array. For getting Entity objects, use EntityProvider::getEntitiesFromSitelinks
	 * @param string[] $sites identifier for the site on which the corresponding page resides
	 * @param string[] $titles the title of the corresponding page
	 * @param string[] $languages Languages for labels/descriptions
	 * @return Entity[]
	 * @throws Exception
	 */
	public function getEntitiesFromSitelinks( array $sites, array $titles, array $languages = array() ) {
		$params = array(
			'action' => 'wbgetentities',
			'sites' => implode( $sites, '|' ),
			'titles' => implode( $titles, '|' )
		);
		if( $languages !== array() ) {
			$params['languages'] = implode( $languages, '|' );
		}
		$response = $this->get( $params );
		return $this->parseGetEntitiesApiResponse( $response );
	}

	/**
	 * @deprecated use EntityProvider::getEntityFromSitelink
	 * @param string $site identifier for the site on which the corresponding page resides
	 * @param string $title the title of the corresponding page
	 * @param string[] $languages Languages for labels/descriptions
	 * @return Entity|null
	 * @throws Exception
	 */
	public function getEntityFromSitelink( $site, $title, array $languages = array() ) {
		$entities = $this->getEntitiesFromSitelinks( array( $site ), array( $title ), $languages );
		foreach( $entities as $entity ) {
			return $entity;
		}
		return null;
	}

	/**
	 * @param array $result the wbgetentities api response
	 * @return Entity[]
	 */
	protected function parseGetEntitiesApiResponse( array $result ) {
		$entities = array();
		if( isset( $result['entities'] ) ) {
			foreach( $result['entities'] as $data ) {
				if( !isset( $data['missing'] ) ) {
					$entities[$data['id']] = Entity::newFromArray( $this, $data );
				}
			}
		}
		return $entities;
	}

	/**
	 * @param string|null $id id of the entity edited. null to reate a new entity
	 * @param array $data data to be sets
	 * @param integer|null $baseRevisionId The numeric identifier for the revision to base the modification on
	 * @param string $summary summary for the change
	 * @throws Exception
	 */
	public function editEntity( $id = null, array $data = array(), $baseRevisionId = null, $summary = '' ) {
		$params = array(
			'action' => 'wbeditentity'
		);
		if( $id !== null ) {
			$params['id'] = $id;
		}
		$postParams = array(
			'data' => json_encode( $data )
		);
		return $this->editAction( $params, $postParams, $baseRevisionId, $summary );
	}

	/**
	 * @param string $entity id of the entity you are adding the claim to
	 * @param string $snakType the type of the snak
	 * @param string $property id of the snak property
	 * @param mixed|null $value value of the snak when creating a claim with a snak that has a value
	 * @param integer|null $baseRevisionId The numeric identifier for the revision to base the modification on
	 * @param string $summary summary for the change
	 * @throws Exception
	 */
	public function createClaim( $entity, $snakType, $property, $value = null, $baseRevisionId = null, $summary = '' ) {
		$params = array(
			'action' => 'wbcreateclaim',
			'entity' => $entity,
			'snaktype' => $snakType,
			'property' => $property
		);
		if( $value !== null ) {
			$params['value'] = json_encode( $value );
		}
		return $this->editAction( $params, array(), $baseRevisionId, $summary );
	}

	/**
	 * @param string $claim GUID identifying the claim
	 * @param string $snakType the type of the snak
	 * @param mixed|null $value the value to set the datavalue of the the main snak of the claim to
	 * @param integer|null $baseRevisionId The numeric identifier for the revision to base the modification on
	 * @param string $summary summary for the change
	 * @throws Exception
	 */
	public function setClaimValue( $claim, $snakType, $value = null, $baseRevisionId = null, $summary = '' ) {
		$params = array(
			'action' => 'wbsetclaimvalue',
			'claim' => $claim,
			'snaktype' => $snakType
		);
		if( $value !== null ) {
			$params['value'] = json_encode( $value );
		}
		return $this->editAction( $params, array(), $baseRevisionId, $summary );
	}

	/**
	 * @param string[] $claims array of GUID identifying the claim
	 * @param integer|null $baseRevisionId The numeric identifier for the revision to base the modification on
	 * @param string $summary summary for the change
	 * @throws Exception
	 */
	public function removeClaims( array $claims, $baseRevisionId = null, $summary = '' ) {
		$params = array(
			'action' => 'wbremoveclaims',
			'claim' => implode( $claims, '|' )
		);
		return $this->editAction( $params, array(), $baseRevisionId, $summary );
	}

	/**
	 * @param string $statement GUID identifying the statement
	 * @param string $snaks the snaks to set the reference to. JSON object with property ids pointing to arrays containing the snaks for that property
	 * @param string $reference a hash of the reference that should be updated. When not provided, a new reference is created
	 * @param integer|null $baseRevisionId The numeric identifier for the revision to base the modification on
	 * @param string $summary summary for the change
	 * @throws Exception
	 */
	public function setReference( $statement, $snaks, $reference = null, $baseRevisionId = null, $summary = '' ) {
		$params = array(
			'action' => 'wbsetreference',
			'statement' => $statement,
			'snaks' => $snaks
		);
		if( $reference !== null ) {
			$params['reference'] = $reference;
		}
		return $this->editAction( $params, array(), $baseRevisionId, $summary );
	}

	/**
	 * @param string[] $params params used for the edition
	 * @param string[] $postParams params to POST
	 * @param integer|null $baseRevisionId The numeric identifier for the revision to base the modification on
	 * @param string $summary summary for the change
	 * @return array
	 * @throws Exception
	 * @todo error
	 */
	protected function editAction( $params, $postParams = array(), $baseRevisionId = null, $summary = '' ) {
		$params['token'] = $this->getEditToken();
		if($baseRevisionId !== null) {
			$params['baserevid'] = $baseRevisionId;
		}
		if( $summary !== '' ) {
			$params['summary'] = $summary;
		}
		if( $this->botEdits ) {
			$params['bot'] = true;
		}
		//Limit number of edits
		$time = time();
		if( $this->lastEditTimestamp > 0 && ($time - $this->lastEditTimestamp) < $this->editLaps ) {
			$wait = $this->lastEditTimestamp + $this->editLap - $time;
			echo "\nWait for $wait seconds...\n";
			sleep( $wait );
		}
		$this->editTimestamp = time();

		$result = $this->post( $params,  $postParams );
		return $result;
	}
}
