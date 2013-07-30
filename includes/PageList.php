<?php

namespace Addframe;

use Addframe\Page;

/**
 * Class PageList, Represents a list of pages
 *
 * @author Addshore
 */
class PageList {

	/**
	 * @var array The pages in the list
	 */
	private $entries;

	/**
	 * @param null|Array|Page $values to be added to the page list at the start
	 */
	function __construct( $values = null ) {
		if ( ! $values === null ){
			if( is_array( $values ) ){
				/* @var $page Page */
				foreach( $values as $page ){
					$this->addPage( $page );
				}
			} else {
				if( $values instanceof Page ){
					$this->addPage( $values );
				}
			}
		}
	}

	public function toArray (){
		return $this->entries;
	}

	public function addPage( $page ) {
		$this->entries[] = $page;
	}

	public function addArray( $pages ){
		foreach( $pages as $page ){
			$this->addPage( $page );
		}
	}

	private function removeKey( $key ) {
		if( array_key_exists( $key, $this->entries ) ){
			unset( $this->entries[$key] );
		}
	}

	/**
	 * @param $key
	 * @return Page
	 */
	public function getPageWithkey( $key ){
		if( array_key_exists( $key, $this->entries ) ){
			return $this->entries[$key];
		}
		return null;
	}

	/**
	 * This makes the list unique using sitelang, sitetype and title(en,wiki,Wikipedia:Sandbox)
	 */
	public function makeUniqueFromPage( ){
		$index = array();
		/* @var $page Page */
		foreach( $this->entries as $key => $page ){
			$sig = array( $page->title, $page->site->getLanguage(), $page->site->getType() );
			if( in_array( $sig , $index ) ){
				$this->removeKey( $key );
			} else {
				$index[] = $sig;
			}
		}
	}

	/**
	 * This makes the list unique using sitelang and sitetype (en,wiki)
	 */
	public function makeUniqueFromSite( ){
		$index = array();
		/* @var $page Page */
		foreach( $this->entries as $key => $page ){
			$sig = array( $page->site->getLanguage(), $page->site->getType() );
			if( in_array( $sig , $index ) ){
				$this->removeKey( $key );
			} else {
				$index[] = $sig;
			}
		}
	}

}