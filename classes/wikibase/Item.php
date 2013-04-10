<?php

/**
 * An item.
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
class Item extends Entity {
	
	/**
	 * @var array site => title
	 */
	protected $sitelinks = array();

	protected function fillData( array $data ) {
		parent::fillData( $data );
		if( isset( $data['sitelinks'] ) ) {
			$this->sitelinks = array();
			foreach( $data['sitelinks'] as $val ) {
				$this->sitelinks[$val['site']] = $val['title'];
			}
		}
	}

	/**
	 * @return array site => title
	 */
	public function getSitelinks() {
		return $this->sitelinks;
	}

	/**
	 * @param string $language the language code
	 * @return string|null null when the link doesn't exist
	 */
	public function getSitelink( $language ) {
		return isset( $this->sitelinks[$language] ) ? $this->sitelinks[$language] : null;
	}

	/**
	 * @param string $site site id like "frwiki"
	 * @param string $title title of the page. Use '' to remove the link
	 */
	public function setSitelink( $site, $title ) {
		if( $title === '' ) {
			unset( $this->sitelinks[$site] );
		} else {
			$this->sitelinks[$site] = $title;
		}
		$this->changes['sitelinks'][$site] = array(
			'site' => $site,
			'title' => $title
		);
	}

	/**
	 * @param Snak the snak use as main snak
	 * @throws Exception
	 */
	public function createStatementForSnak( Snak $snak ) {
		return Claim::newFromSnak( $this, $snak, 'statement' );
	}
}
