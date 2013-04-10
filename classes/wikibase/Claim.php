<?php

/**
 * A claim.
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
class Claim {

	/**
	 * @var Entity
	 */
	protected $entity;

	/**
	 * @var string|null
	 */
	protected $id;

	/**
	 * @var string the id used internally
	 */
	protected $internalId;

	/**
	 * @var Snak
	 */
	protected $mainSnak;

	/**
	 * @var Snak[]
	 */
	protected $qualifiers = array();

	/**
	 * @var array
	 */
	protected $changes = array();

	/**
	 * @protected
	 * @param Entity $entity
	 * @param array $data
	 */
	public function __construct( Entity $entity, array $data ) {
		$this->entity = $entity;
		$this->fillData( $data );
	}

	protected function fillData( array $data ) {
		if( isset( $data['mainsnak'] ) ) {
			$this->mainSnak = Snak::newFromArray( $data['mainsnak'] );
		}
		if( isset( $data['id'] ) ) {
			$this->id = $data['id'];
		}
		if( $this->internalId === null ) {
			if( $this->id !== null ) {
				$this->internalId = $this->id;
			} else {
				$this->internalId = time() . $this->mainSnak->getPropertyId() . $this->mainSnak->getDataValue(); //TODO improve
			}
		}
	}

	/**
	 * @param Entity $entity
	 * @param Snak $snak snak to be used as main snak
	 * @param string $type claim type
	 * @return Claim
	 * @throws Exception
	 */
	public function newFromSnak( Entity $entity, Snak $snak, $type = 'claim' ) {
		$claim = self::newFromArray( $entity, array(
			'mainsnak' => $snak->toArray(),
			'type' => $type
		) );
		$claim->changes = array(
			'mainsnak' => $snak->toArray()
		);
		$entity->addClaim( $claim );
		return $claim;
	}

	/**
	 * @param Entity $entity
	 * @param array $data
	 * @return Claim
	 * @throws Exception
	 */
	public function newFromArray( Entity $entity, array $data ) {
		if( isset( $data['type'] ) ) {
			switch( $data['type'] ) {
				case 'statement':
					return new Statement( $entity, $data );
				default:
					return new self( $entity, $data );
			}
		}
		throw new Exception( 'Unknown type!' );
	}

	/**
	 * @return string
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * @protected
	 * @return string
	 */
	public function getInternalId() {
		return $this->internalId;
	}

	/**
	 * @return Snak
	 */
	public function getMainSnak() {
		return $this->mainSnak;
	}

	/**
	 * @param Snak a snak with the same property
	 * @throw Exception
	 */
	public function setMainSnak( Snak $snak ) {
		if( !$this->mainSnak->getPropertyID()->equals( $snak->getPropertyID() ) ) {
			throw new Exception( 'Different property id!' );
		}
		$this->mainSnak = $snak;
		$this->changes['mainsnak'] = $snak->toArray();
	}

	/**
	 * @param string $summary summary for the change
	 * @throws Exception
	 */
	public function save( $summary = '' ) {
		if( $this->changes === array() ) {
			return; //Nothing to do
		}

		if( isset( $this->changes['mainsnak'] ) ) {
			if( !isset( $this->changes['mainsnak']['snaktype'] ) || !isset( $this->changes['mainsnak']['property'] ) ) {
				throw new Exeption( 'The main snak does not have required data' );
			}
			$value = isset( $this->changes['mainsnak']['datavalue'] ) ? $this->changes['mainsnak']['datavalue']['value'] : null;

			if( $this->id === null ) {
				//create claim
				$result = $this->entity->getApi()->createClaim( $this->entity->getId()->getPrefixedId(), $this->changes['mainsnak']['snaktype'], $this->changes['mainsnak']['property'], $value, $this->entity->getLastRevisionId(), $summary );
			} else {
				$result = $this->entity->getApi()->setClaimValue( $this->id, $this->changes['mainsnak']['snaktype'], $value, $this->entity->getLastRevisionId(), $summary );
			}
			$this->updateDataFromResult( $result );
			unset( $this->changes['mainsnak'] );
		}
	}

	/**
	 * Update data from the result of an API call
	 */
	protected function updateDataFromResult( $result ) {
		if( isset( $result['claim'] ) ) {
			$this->fillData( $result['claim'] );
		}
		if( isset( $result['pageinfo']['lastrevid'] ) ) {
			$this->entity->setLastRevisionId( $result['pageinfo']['lastrevid'] );
		}
	}

	/**
	 * Delete the claim and push the change to the database
	 *
	 * @param string $summary summary for the change
	 * @throws Exception
	 */
	public function deleteAndSave( $summary = '' ) {
		if( $this->id !== null ) {
			$this->entity->getApi()->removeClaims( array( $this->id ), $this->entity->getLastRevisionId(), $summary );
		}
		$this->entity->removeClaim( $this );
	}
}
