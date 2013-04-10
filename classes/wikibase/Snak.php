<?php

/**
 * A snak.
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
 * @todo datavalue managment
 */
class Snak implements Comparable, Copyable {

	/**
	 * @var string
	 */
	protected $type;

	/**
	 * @var string
	 */
	protected $propertyId;

	/**
	 * @var \DataValues\DataValue
	 */
	protected $dataValue;

	/**
	 * string $type type of snak
	 * @param EntityId|string $propertyId id of the property
	 * @param \DataValues\DataValue|null $dataValue value of the property (optional)
	 * @throws Exception
	 */
	public function __construct( $type, $propertyId, \DataValues\DataValue $dataValue = null ) {
		if( is_string( $propertyId ) ) {
			$propertyId = EntityId::newFromPrefixedId( $propertyId );
		}
		if( !( $propertyId instanceof EntityId ) || $propertyId->getEntityType() !== 'property' ) {
			throw new Exception( '$propertyId must be a valid property id' );
		}
		$this->type = $type;
		$this->propertyId = $propertyId;
		$this->dataValue = $dataValue;
	}

	/**
	 * @param array $data
	 * @return Snak
	 * @throws Exception
	 */
	public function newFromArray( array $data ) {
		if( !isset( $data['snaktype'] ) || !isset( $data['property'] ) ) {
			throw new Exeption( 'Invalid Snak serialization' );
		}
		$dataValue = isset( $data['datavalue'] ) ? \DataValues\DataValueFactory::singleton()->newDataValue( $data['datavalue']['type'], $data['datavalue']['value'] ) : null;
		return new self( $data['snaktype'], $data['property'], $dataValue );
	}

	/**
	 * @return string
	 */
	public function getType() {
		return $this->type;
	}

	/**
	 * @return EntityId
	 */
	public function getPropertyId() {
		return $this->propertyId;
	}

	/**
	 * @return \DataValues\DataValue|null
	 */
	public function getDataValue() {
		return $this->dataValue;
	}

	/**
	 * @return array
	 */
	public function toArray() {
		$array = array(
			'snaktype' => $this->type,
			'property' => $this->propertyId->getPrefixedId()
		);
		if( $this->dataValue !== null ) {
			$array['datavalue'] = $this->dataValue->toArray();
		}
		return $array;
	}

	/**
	 * @see Comparable::equals
	 *
	 * @param mixed $value
	 * @return boolean
	 */
	public function equals( $value ) {
		return $value instanceof self
			&& $this->getType() === $value->getType()
			&& $this->getPropertyId()->equals( $value->getPropertyId() )
			&& ( $this->getDataValue() === null && $value->getDataValue() === null ) || ( $this->getDataValue()->equals( $value->getDataValue() ) );
	}

	/**
	 * @see Copyable::getCopy
	 *
	 * @return Snak
	 */
	public function getCopy() {
		$value = ( $this->dataValue !== null ) ? $this->dataValue->getCopy() : null;
		return new self( $this->type, $this->propertyId, $value );
	}
}
