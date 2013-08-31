<?php

namespace Addframe\Mediawiki;

/**
 * Class SiteList
 * @license GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 * @author Addshore
 */

class SiteList extends \ArrayObject {

	/**
	 * Site urls pointing to their sites offset value.
	 *
	 * @var array of integer
	 */
	protected $byUrl = array();

	/**
	 * @see SiteList::getNewOffset()
	 * @var integer
	 */
	protected $indexOffset = 0;

	/**
	 * Returns the type of object allowed in the list
	 *
	 * @source mediawiki/includes/site/SiteList
	 *
	 * @return string
	 */
	public function getObjectType() {
		return 'Addframe\Mediawiki\Site';
	}

	/**
	 * Returns if the provided value has the same type as the elements
	 * that can be added to this ArrayObject.
	 *
	 * @source mediawiki/includes/site/SiteList
	 *
	 * @param mixed $value
	 *
	 * @return boolean
	 */
	public function hasValidType( $value ) {
		$class = $this->getObjectType();
		return $value instanceof $class;
	}

	/**
	 * Method that actually sets the element and holds
	 * all common code needed for set operations, including
	 * type checking and offset resolving.
	 *
	 * If you want to do additional indexing or have code that
	 * otherwise needs to be executed whenever an element is added,
	 * you can overload @see preSetElement.
	 *
	 * @source mediawiki/includes/site/GenericArrayObject
	 *
	 * @param mixed $index
	 * @param mixed $value
	 *
	 * @throws \InvalidArgumentException
	 */
	protected function setElement( $index, $value ) {
		if ( !$this->hasValidType( $value ) ) {
			throw new \InvalidArgumentException(
				'Can only add ' . $this->getObjectType() . ' implementing objects to ' . get_called_class() . '.'
			);
		}

		if ( is_null( $index ) ) {
			$index = $this->getNewOffset();
		}

		if ( $this->preSetElement( $index, $value ) ) {
			parent::offsetSet( $index, $value );
		}
	}

	/**
	 * Finds a new offset for when appending an element.
	 * The base class does this, so it would be better to integrate,
	 * but there does not appear to be any way to do this...
	 *
	 * @source mediawiki/includes/site/GenericArrayObject
	 *
	 * @return integer
	 */
	protected function getNewOffset() {
		while ( $this->offsetExists( $this->indexOffset ) ) {
			$this->indexOffset++;
		}

		return $this->indexOffset;
	}

	/**
	 * @see ArrayObject::append
	 * @param mixed $value
	 */
	public function append( $value ) {
		$this->setElement( null, $value );
	}

	/**
	 * Gets called before a new element is added to the ArrayObject.
	 *
	 * Should return a boolean. When false is returned the element
	 * does not get added to the ArrayObject.
	 *
	 * @source mediawiki/includes/site/SiteList
	 *
	 * @param int|string $index
	 * @param Site $site
	 *
	 * @return boolean
	 */
	protected function preSetElement( $index, $site ) {
		if ( $this->hasSite( $site->getUrl() ) ) {
			$this->removeSite( $site->getUrl() );
		}

		$this->byUrl[$site->getUrl()] = $index;

		return true;
	}

	/**
	 * Returns if the list contains the site with the provided global site identifier.
	 *
	 * @source mediawiki/includes/site/SiteList
	 *
	 * @param string $url
	 *
	 * @return boolean
	 */
	public function hasSite( $url ) {
		return array_key_exists( $url, $this->byUrl );
	}

	/**
	 * Returns if the list contains no sites.
	 *
	 * @source mediawiki/includes/site/SiteList
	 *
	 * @return boolean
	 */
	public function isEmpty() {
		return $this->byUrl === array();
	}

	/**
	 * Removes the site with the specified global site identifier.
	 * The site needs to exist, so if not sure, call hasGlobalId first.
	 *
	 * @source mediawiki/includes/site/SiteList
	 *
	 * @param string $url
	 */
	public function removeSite( $url ) {
		$this->offsetUnset( $this->byUrl[$url] );
	}

	/**
	 * @see ArrayObject::offsetUnset()
	 *
	 * @source mediawiki/includes/site/SiteList
	 *
	 * @param mixed $index
	 */
	public function offsetUnset( $index ) {
		if ( $this->offsetExists( $index ) ) {
			/**
			 * @var Site $site
			 */
			$site = $this->offsetGet( $index );

			unset( $this->byUrl[$site->getUrl()] );
		}

		parent::offsetUnset( $index );
	}

	/**
	 * Returns the Site with the provided global site identifier.
	 * The site needs to exist, so if not sure, call hasGlobalId first.
	 *
	 * @source mediawiki/includes/site/SiteList
	 *
	 * @param string $url
	 *
	 * @return Site
	 */
	public function getSite( $url ) {
		return $this->offsetGet( $this->byUrl[$url] );
	}

	/**
	 * @param $sites Site[]
	 * @return SiteList
	 */
	public static function newFromArray( $sites ){
		$siteList = new SiteList( );
		foreach( $sites as $site ){
			$siteList->append( $site );
		}
		return $siteList;
	}

}