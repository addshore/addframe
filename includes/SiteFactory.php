<?php

/**
 * Class SiteFactory
 * Creates sites and adds them to the site registry
 */
class SiteFactory extends Registry{

	/**
	 * @param $url string url pointing to the site
	 * @return Mediawiki
	 */
	function addSite( $url ){
		$this->$url = new Mediawiki($url);;
		return $this->$url;
	}

	/**
	 * @param $url
	 * @return Mediawiki
	 */
	function getSite( $url ){
		return $this->$url;
	}
}