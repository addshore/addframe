<?php

namespace Addframe;

/**
 * Class Family for a collection of  mediawiki sites
 */
class Family extends Registry {

	/** @var UserLogin */
	private $login;
	/** * @var array of sites with the following keys
	 * [url][wikiid][code][sitename][closed][private] */
	private $siteMatrix;
	/** @var array Index of siteMatrix with keys (url|dbname) */
	private $siteMatrixIndex;
	/** @var Site[] List of sites in the family */
	private $sites;
	/** * @var Site the home site for the family */
	private $homeSite;

	/**
	 * Create an object to hold a family of sites
	 *
	 * @param null $globalLogin
	 * @param null $homeUrl
	 */
	public function __construct( $globalLogin = null, $homeUrl = null ) {
		if ( isset( $homeUrl ) ) {
			$this->homeSite = $this->addSite( $homeUrl );
		}
		if ( isset( $globalLogin ) ) {
			$this->login = $globalLogin;
			$this->homeSite->setLogin( $globalLogin );
		}
	}

	/**
	 * @return UserLogin
	 */
	public function getLogin() {
		return $this->login;
	}

	/**
	 * @return array
	 */
	private function getSiteMatrix() {
		if ( $this->siteMatrix == null ){
			$this->siteMatrix = $this->homeSite->requestSitematrix();
			$this->buildSiteIndex();
		}
		return $this->siteMatrix;
	}


	/**
	 * Builds the index used for looking up site details
	 */
	private function buildSiteIndex() {
		$index = array();
		foreach( $this->siteMatrix as $groupKey => $group){
			if( $groupKey == 'count' || $groupKey == 'specials' ) { continue; }
			foreach( $group['site'] as $siteKey => $site ){
				$cleanUrl = str_replace( array('http://','https://','//'), '', $site['url'] );
				$index['url'][$cleanUrl] = array( $groupKey, $siteKey );
				$index['dbname'][$site['dbname']] = array( $groupKey, $siteKey );
			}
		}
		$this->siteMatrixIndex = $index;
	}

	/**
	 * Uses the key and value to find siteDetails in the SiteMatrix
	 * @param $key string (url|dbname)
	 * @param $value string value of dbname or url
	 * @return array
	 */
	public function getSiteDetailsFromSiteIndex( $key, $value ){
		if( !isset( $this->siteMatrixIndex[$key][$value] ) ){
			return null;
		}
		$indexValue = $this->siteMatrixIndex[$key][$value];
		return $this->siteMatrix[ $indexValue[0] ]['site'][ $indexValue[1] ];
	}

	/**
	 * @param $url
	 * @return Site
	 */
	public function getSite( $url ) {
		if ( ! isset( $this->sites[$url] ) ) {
			$this->addSite( $url );
		}
		return $this->sites[$url];
	}

	/**
	 * @param $siteid string of the siteid for the site
	 * @return Site
	 */
	public function getSiteFromSiteid( $siteid ) {
		$this->getSiteMatrix();
		if ( isset( $this->siteMatrixIndex['dbname'][$siteid] ) ) {
			$siteData = $this->getSiteDetailsFromSiteIndex( 'dbname', $siteid );

			if ( ! isset( $this->sites[ $siteData['url'] ] ) ) {
				$url = $cleanUrl = str_replace( array('http://','https://','//'), '', $siteData['url'] );
				$this->addSite( $url );
				return $this->getSite( $url );
			}
		}
	}

	/**
	 * @param $url string of the site that we want to add to the family
	 * @return Site
	 */
	public function addSite( $url ) {
		if( ! $this->sites[$url] instanceof Site ){
			echo "Loading $url\n";
			$this->sites[$url] = new Site( $url, $this );
			if ( isset( $this->login ) ) {
				$this->sites[$url]->setLogin( $this->login );
			}
		}

		return $this->sites[$url];
	}

}