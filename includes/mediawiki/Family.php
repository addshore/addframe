<?php

namespace Addframe\Mediawiki;
use Addframe\Http;
use Addframe\Mediawiki\Api\SitematrixRequest;

/**
 * Class Family, representing a sitematrix of sites
 */
class Family extends Site {

	/**
	 * @var SiteList sites in the family
	 */
	protected $siteList;

	protected $siteIndex;

	/**
	 * This should generally not be used, use Family::new* instead
	 */
	/* protected */ public function __construct( $http = null ) {
		$this->siteList = new SiteList();
		$this->siteIndex = array();
		parent::__construct( $http );
	}

	/**
	 * Gets the sitelist for the family
	 * @param null|string|string[] $filter types of site to return in the sitelist (closed|private|fishbowl|active)
	 * @return SiteList
	 */
	public function getSiteList( $filter = null ){
		if( $this->siteList->isEmpty() === true ){
			$siteMatrix = $this->getSiteMatrix();
			$this->generateSiteListFromMatrix( $siteMatrix );
		}
		if( is_null( $filter ) ){
			return $this->siteList;
		} else {
			if( is_string( $filter ) ){
				$filter = array( $filter );
			}
			$partialSiteList = new SiteList();
			foreach( $filter as $filterBy ){
				if( array_key_exists( $filterBy, $this->siteIndex ) ){
					foreach( $this->siteIndex[$filterBy] as $siteUrl ){
						$partialSiteList->append( $this->siteList->getSite( $siteUrl ) );
					}
				}
			}
			return $partialSiteList;
		}

	}

	/**
	 * Returns the sitematrix
	 * @return array
	 */
	public function getSiteMatrix(){
		$apiResult = $this->getApi()->doRequest( new SitematrixRequest() );
		return $apiResult['sitematrix'];
	}

	public function hasSite( $url ){
		return $this->siteList->hasSite( $url );
	}

	/**
	 * Generates the SiteList from a sitematrix array
	 * @param $siteMatrix array
	 */
	protected function generateSiteListFromMatrix( $siteMatrix ){

		foreach( $siteMatrix as $groupKey => $group){
			if( is_int( $groupKey ) ) {
				if( array_key_exists( 'site', $group ) ){
					foreach( $group['site'] as $site ){
						$this->addSiteFromSitematrixSite( $site );
					}
				}
			} else if ( $groupKey == 'specials' ) {
				foreach( $group as $site ){
					$this->addSiteFromSitematrixSite( $site );
				}
			}
		}
	}

	/**
	 * Adds site from a sitematrix site array to the sitelist
	 * @param $site array
	 */
	protected function addSiteFromSitematrixSite( $site ) {
		if( array_key_exists( 'url', $site ) ){
			$site['url'] = str_replace( array('http://','https://','//'), '', $site['url'] );
			$this->siteList->append( Site::newFromUrl( $site['url'] ) );
			$this->addIndexForSitematrixSite( $site );
		}
	}

	/**
	 * Adds indexs for a given sitematrix site array
	 * @param $site array
	 */
	protected function addIndexForSitematrixSite( $site ) {
		//todo add other indexes such as dbname, code, sitename, localname
		if( array_key_exists( 'closed', $site ) ){
			$this->siteIndex['closed'][] = $site['url'];
		} else if( array_key_exists( 'private', $site ) ){
			$this->siteIndex['private'][] = $site['url'];
		} else if( array_key_exists( 'fishbowl', $site ) ){
			$this->siteIndex['fishbowl'][] = $site['url'];
		} else {
			$this->siteIndex['active'][] = $site['url'];
		}
	}

}