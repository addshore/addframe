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
		$site = new Mediawiki($url);
		$wikiid = $site->wikiid;
		$this->$wikiid = $site;
		return $this->$wikiid;
	}

	/**
	 * @param $wikiid
	 * @return Mediawiki
	 */
	function getSite( $wikiid ){
		return $this->$wikiid;
	}
}