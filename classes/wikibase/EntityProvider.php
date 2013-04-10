<?php

/**
 * Class for getting entities from various requests.
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
class EntityProvider {

	/**
	 * @var WikibaseApi
	 */
	protected $api;

	/**
	 * @param WikibaseApi $api the API to use
	 */
	public function __construct( WikibaseApi $api ) {
		$this->api = $api;
	}

	/**
	 * @param EntityId[] $ids the IDs of the entities to get the data from
	 * @param string[] $languages languages for labels/descriptions
	 * @return Entity[]
	 * @throws Exception
	 * @todo Error management
	 */
	public function getEntitiesFromIds( array $ids, array $languages = array() ) {
		return $this->api->getEntitiesFromIds( $ids, $languages );
	}

	/**
	 * @param EntityId $id the ID of the entities to get the data from
	 * @param string[] $languages languages for labels/descriptions
	 * @return Entity|null
	 * @throws Exception
	 */
	public function getEntityFromId( EntityId $id, array $languages = array() ) {
		$entities = $this->getEntitiesFromIds( array( $id ), $languages );
		foreach( $entities as $entity ) {
			return $entity;
		}
		return null;
	}

	/**
	 * @param string[] $sites identifier for the site on which the corresponding page resides
	 * @param string[] $titles the title of the corresponding page
	 * @param string[] $languages Languages for labels/descriptions
	 * @return Entity[]
	 * @throws Exception
	 */
	public function getEntitiesFromSitelinks( array $sites, array $titles, array $languages = array() ) {
		return $this->api->getEntitiesFromSitelinks( $sites, $titles, $languages );
	}

	/**
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
}
