<?php

/**
 * An entity.
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
abstract class Entity {

	/**
	 * @var EntityId
	 */
	protected $id = null;

	/**
	 * @var integer|null
	 */
	protected $lastRevisionId = null;

	/**
	 * @var array language => value
	 */
	protected $labels = array();

	/**
	 * @var array language => values
	 */
	protected $aliases = array();

	/**
	 * @var array language => value
	 */
	protected $descriptions = array();

	/**
	 * @var array property id => Claim[]
	 */
	protected $claims = array();

	/**
	 * @var array
	 */
	protected $changes = array();

	/**
	 * @var WikibaseApi
	 */
	protected $api;

	/**
	 * @protected
	 * @param WikibaseApi $api
	 * @param array $data
	 */
	public function __construct( WikibaseApi $api, array $data ) {
		$this->api = $api;
		$this->fillData( $data );
	}

	protected function fillData( array $data ) {
		if( isset( $data['id'] ) ) {
			$this->id = EntityId::newFromPrefixedId( $data['id'] );
		}
		if( isset( $data['lastrevid'] ) ) {
			$this->lastRevisionId = $data['lastrevid'];
		}
		if( isset( $data['labels'] ) ) {
			$this->labels = array();
			foreach( $data['labels'] as $val ) {
				$this->labels[$val['language']] = $val['value'];
			}
		}
		if( isset( $data['aliases'] ) ) {
			$this->aliases = array();
			foreach( $data['aliases'] as $lang => $value ) {
				$this->aliases[$lang] = array();
				foreach( $value as $val ) {
					$this->aliases[$val['language']][] = $val['value'];
				}
			}
		}
		if( isset( $data['descriptions'] ) ) {
			$this->descriptions = array();
			foreach( $data['descriptions'] as $val ) {
				$this->descriptions[$val['language']] = $val['value'];
			}
		}
		if( isset( $data['claims'] ) ) {
			$this->claims = array();
			foreach( $data['claims'] as $prop => $list ) {
				$this->claims[$prop] = array();
				foreach( $list as $val ) {
					$claim = Claim::newFromArray( $this, $val );
					$this->claims[$prop][$claim->getInternalId()] = $claim;
				}
			}
		}
	}

	/**
	 * @param WikibaseApi $api
	 * @return Entity
	 */
	public function newEmpty( WikibaseApi $api ) {
		return new self( $api, array() );
	}

	/**
	 * @param WikibaseApi $api
	 * @param array $data
	 * @return Entity
	 * @throws Exception
	 */
	public function newFromArray( WikibaseApi $api, array $data ) {
		if( isset( $data['type'] ) ) {
			switch( $data['type'] ) {
				case 'item':
					return new Item( $api, $data );
				case 'property':
					return new Property( $api, $data );
			}
		}
		throw new Exception( 'Unknown type!' );
	}

	/**
	 * @return EntityId
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * @return array language => value
	 */
	public function getLabels() {
		return $this->labels;
	}

	/**
	 * @param string $language the language code
	 * @return string|null null when the label doesn't exist
	 */
	public function getLabel( $language ) {
		return isset( $this->labels[$language] ) ? $this->labels[$language] : null;
	}

	/**
	 * @param string $language Language code
	 * @param string $value label. '' for removing the label
	 */
	public function setLabel( $language, $value ) {
		if( $value === '' ) {
			unset( $this->labels[$language] );
		} else {
			$this->labels[$language] = $value;
		}
		$this->changes['labels'][$language] = array(
			'language' => $language,
			'value' => $value
		);
	}

	/**
	 * @return array language => values
	 */
	public function getAliases() {
		return $this->aliases;
	}

	/**
	 * @param string $language the language code
	 * @return array empty array when there is no aliases
	 */
	public function getAlias( $language ) {
		return isset( $this->aliases[$language] ) ? $this->aliases[$language] : array();
	}

	/**
	 * @param string $language Language code
	 * @param string $value alias to add
	 */
	public function addAlias( $language, $value ) {
		$this->aliases[$language][] = $value;

		//Override if needed an action on the same alias
		if( isset( $this->changes['aliases'] ) ) {
			foreach( $this->changes['aliases'] as $key => $val ) {
				if( $val['language'] === $language && $val['value'] === $value ) {
					unset( $this->changes['aliases'][$key] );
					break;
				}
			}
		}

		$this->changes['aliases'][] = array(
			'language' => $language,
			'value' => $value,
			'add' => true
		);
	}

	/**
	 * @param string $language Language code
	 * @param string $value alias to remove
	 */
	public function removeAlias( $language, $value ) {
		$key = array_search( $value, $this->aliases[$language] );
		if( $key !== false ) {
    		unset( $this->aliases[$language][$key] );

			//Override if needed an action on the same alias
			if( isset( $this->changes['aliases'] ) ) {
				foreach( $this->changes['aliases'] as $key => $val ) {
					if( $val['language'] === $language && $val['value'] === $value ) {
						unset( $this->changes['aliases'][$key] );
						break;
					}
				}
			}

			$this->changes['aliases'][] = array(
				'language' => $language,
				'value' => $value,
				'remove' => true
			);
		}
	}

	/**
	 * @return array language => value
	 */
	public function getDescriptions() {
		return $this->descriptions;
	}

	/**
	 * @param string $language the language code
	 * @return string|null null when the description doesn't exist
	 */
	public function getDescription( $language ) {
		return isset( $this->descriptions[$language] ) ? $this->descriptions[$language] : null;
	}

	/**
	 * @param string $language Language code
	 * @param string $value description. '' for removing the description
	 */
	public function setDescription( $language, $value ) {
		if( $value === '' ) {
			unset( $this->descriptions[$language] );
		} else {
			$this->descriptions[$language] = $value;
		}
		$this->changes['descriptions'][$language] = array(
			'language' => $language,
			'value' => $value
		);
	}

	/**
	 * @return array "property id" => Claim[]
	 */
	public function getClaims() {
		return $this->claims;
	}

	/**
	 * @return Claim[]
	 */
	public function getClaimsForProperty( $property ) {
		if( isset( $this->claims[$property] ) ) {
			return $this->claims[$property];
		} else {
			return array();
		}
	}

	/**
	 * @param string $summary summary for the change
	 * @throws Exception
	 * @todo push back changes for all data excepts claims
	 */
	public function save( $summary = '' ) {
		if( $this->changes === array() ) {
			return; //Nothing to do
		}
		$result = $this->api->editEntity( $this->id->getPrefixedId(), $this->changes, $this->lastRevisionId, $summary );
		if( isset( $result['entity'] ) ) {
			$this->fillData( $result['entity'] );
		}
		$this->changes = array();
	}

	/**
	 * @protected
	 * @return WikibaseApi the API used
	 */
	public function getApi() {
		return $this->api;
	}

	/**
	 * @protected
	 * @return integer|null the last revision ID
	 */
	public function getLastRevisionId() {
		return $this->lastRevisionId;
	}

	/**
	 * @protected
	 * @param $lastRevisionId integer the last revision ID
	 */
	public function setLastRevisionId( $lastRevisionId ) {
		$this->lastRevisionId = $lastRevisionId;
	}

	/**
	 * @protected
	 * @param Claim $claim
	 */
	public function addClaim( Claim $claim ) {
		$this->claims[$claim->getMainSnak()->getPropertyId()->getPrefixedId()][$claim->getInternalId()] = $claim;
	}

	/**
	 * It's not the method you are looking for. For deleting a claim and push the change to the database, use Claimm::deleteAndSave
	 *
	 * @protected
	 * @param Claim $claim
	 */
	public function removeClaim( Claim $claim ) {
		unset( $this->claims[$claim->getMainSnak()->getPropertyId()->getPrefixedId()][$claim->getInternalId()] );
	}
}
