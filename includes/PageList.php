<?php

namespace Addframe;

/**
 * Class PageList, Represents a list of pages
 *
 * @author Addshore
 */
class PageList extends \ArrayObject{

	/**
	 * @param null|Array|Page $value to be added to the page list at the start
	 */
	function __construct( $value = null ) {
		if ( $value !== null ){
			if( is_array( $value ) ){
				/* @var $page Page */
				foreach( $value as $page ){
					$this[] = $page;
				}
			} else {
				if( $value instanceof Page ){
					$this[] = $value;
				}
			}
		}
	}

	/**
	 * @param $pages Page[]
	 */
	public function appendArray( $pages ){
		foreach( $pages as $page ){
			$this[] = $page ;
		}
	}

	/**
	 * This makes the list unique using sitelang, sitetype and title(en,wiki,Wikipedia:Sandbox)
	 */
	public function makeUniqueUsingPageDetails( ){
		$index = array();
		/* @var $page Page */
		foreach( $this->getArrayCopy() as $key => $page ){
			$sig = array( $page->getTitle(), $page->site->getLanguage(), $page->site->getType() );
			if( in_array( $sig , $index ) ){
				$this->offsetUnset( $key );
			} else {
				$index[] = $sig;
			}
		}
	}

	/**
	 * This makes the list unique using sitelang and sitetype (en,wiki)
	 */
	public function makeUniqueUsingSiteDetails( ){
		$index = array();
		/* @var $page Page */
		foreach( $this->getArrayCopy() as $key => $page ){
			$sig = array( $page->site->getLanguage(), $page->site->getType() );
			if( in_array( $sig , $index ) ){
				$this->offsetUnset( $key );
			} else {
				$index[] = $sig;
			}
		}
	}

}