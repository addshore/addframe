<?php

namespace Addframe\Mediawiki;

/**
 * Represents a wiki Category
 * @since 0.0.2
 * @author Addshore
 **/

class Category extends Page{

	/**
	 * @param Page $page
	 * @return Category
	 */
	public static function newFromPage( $page ) {
		$cat = new Category( $page->getSite(), $page->getTitle() );
		$cat->ns = 14; // Force it
		return $cat;
	}

	public function getCategoryMembers( $limit = 5000, $recursive = true ){
		echo "Getting members of ".$this->title."\n";
		$returnArray  = array();
		$params['cmtitle'] = $this->title;
		$params['cmlimit'] = $limit;
//		$params['cmtype'] = 'page|subcat';
		$result = $this->site->api->requestListCategoryMembers( $params );
		$returnArray = array_merge( $returnArray, $result['query']['categorymembers'] );
		foreach( $result['query']['categorymembers'] as $member){
			if($member['ns'] == '14' && $recursive ){
				$innerCat = $this->site->newCategoryFromTitle( $member['title'] );
				if( $innerCat instanceof Category ){
					$returnArray = array_merge( $returnArray, $innerCat->getCategoryMembers() );
				}

			}
		}
		return $returnArray;
	}

	/**
	 * Use generator if we can...
	 * @param bool|int $recursive
	 * @return CategoryGenerator
	 */
	public function getMembers( $recursive = false ) {
		$gen = new CategoryGenerator( $this->getSite(), $this );
		if ( !$recursive ) {
			return $gen;
		}
		foreach ( $gen as $page ) {
			if ( $page->getNamespace() == 14 ) {
				$cat = Category::newFromPage( $page );
				$nr = $recursive === true ? $recursive : $recursive - 1;
				$newGen = $cat->getMembers( $nr );
				$gen->extend( $newGen );
			}
		}
		return $gen;
	}

}
