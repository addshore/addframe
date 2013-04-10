<?php

/**
 * Represents an ID of an Entity.
 *
 * An Entity ID consists out of two parts.
 * - The entity type.
 * - A numerical value.
 *
 * The numerical value is sufficient to unequally identify
 * the Entity within a group of Entities of the same type.
 * It is not enough for unique identification in groups
 * of different Entity types, which is where the entity type
 * is also needed.
 *
 * To the outside world these IDs are only exposed in serialized
 * form where the entity type is turned into a prefix to which
 * the numerical part then gets concatenated.
 *
 * Internally the entity type should be used rather then the ID prefix.
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
 *
 * A lot of source code is taken from Wikibase/lib/include/entity/EntityId.php by Jeroen De Dauw and John Erling Blad under GNU GPL v2+
 */
class EntityId extends \DataValues\DataValueObject {

	/**
	 * List of all entites types by their prefix
	 *
	 * @var string[]
	 */
	protected static $entityTypes = array(
		'q' => 'item',
		'p' => 'property'
	);

	/**
	 * The type of the entity to which the ID belongs.
	 *
	 * @var string
	 */
	protected $entityType;

	/**
	 * The numeric ID of the entity.
	 *
	 * @var integer
	 */
	protected $numericId;


	/**
	 * Constructs an EntityId object from a prefixed id.
	 *
	 * @param string $prefixedId
	 * @return EntityId|null
	 * @throws Exception
	 */
	public static function newFromPrefixedId( $prefixedId ) {
		if( preg_match( '/^(\w)(\d+)(#.*|)$/', strtolower( $prefixedId ), $m ) ) {
			if( isset( self::$entityTypes[$m[1]] ) ) {
				return new self( self::$entityTypes[$m[1]], (int) $m[2] );
			}
		}
		return null;
	}

	/**
	 * Constructor.
	 *
	 * @param string $entityType
	 * @param integer $numericId
	 * @throws Exception
	 */
	public function __construct( $entityType, $numericId ) {
		if ( !is_string( $entityType ) ) {
			throw new Exception( '$entityType needs to be a string' );
		}

		if ( !is_integer( $numericId ) ) {
			throw new Exception( '$numericId needs to be an integer' );
		}

		$this->entityType = $entityType;
		$this->numericId = $numericId;
	}

	/**
	 * Returns the prefixed used when serializing the ID.
	 *
	 * @return string
	 */
	public function getPrefix() {
		return array_search( $this->entityType, self::$entityTypes );
	}

	/**
	 * Returns the type of the entity.
	 *
	 * @return string
	 */
	public function getEntityType() {
		return $this->entityType;
	}

	/**
	 * Returns the numeric id of the entity.
	 *
	 * @return integer
	 */
	public function getNumericId() {
		return $this->numericId;
	}

	/**
	 * Gets the serialized ID consisting out of entity type prefix followed by the numerical ID.
	 *
	 * @return string The prefixed id
	 */
	public function getPrefixedId() {
		return $this->getPrefix() . $this->numericId;
	}

	/**
	 * @see Comparable::equals
	 *
	 * @param mixed $target
	 * @return boolean
	 */
	public function equals( $target ) {
		return $target instanceof EntityId
			&& $target->getNumericId() === $this->numericId
			&& $target->getEntityType() === $this->entityType;
	}

	/**
	 * Return a string representation of this entity id. Equal to getPrefixedId().
	 *
	 * @return string
	 */
	public function __toString() {
		return $this->getPrefixedId();
	}

	/**
	 * @see Serializable::serialize
	 *
	 * @return string
	 */
	public function serialize() {
		return json_encode( array( $this->entityType, $this->numericId ) );
	}

	/**
	 * @see Serializable::unserialize
	 *
	 * @param string $value
	 * @return EntityId
	 */
	public function unserialize( $value ) {
		list( $entityType, $numericId ) = json_decode( $value, true );
		$this->__construct( $entityType, $numericId );
	}

	/**
	 * @see DataValue::getType
	 *
	 * @return string
	 */
	public function getType() {
		return 'wikibase-entityid';
	}

	/**
	 * @see DataValue::getSortKey
	 *
	 * @return string|float|int
	 */
	public function getSortKey() {
		return $this->entityType . $this->numericId;
	}

	/**
	 * @see DataValue::getValue
	 *
	 * @return EntityId
	 */
	public function getValue() {
		return $this;
	}

	/**
	 * @see DataValue::getArrayValue
	 *
	 * @return array
	 */
	public function getArrayValue() {
		return array(
			'entity-type' => $this->entityType,
			'numeric-id' => $this->numericId,
		);
	}

	/**
	 * Constructs a new instance of the DataValue from the provided data.
	 * This can round-trip with @see getArrayValue
	 *
	 * @param array $data
	 * @return \DataValues\DataValue
	 */
	public static function newFromArray( $data ) {
		return new self( $data['entity-type'], $data['numeric-id'] );
	}

}
