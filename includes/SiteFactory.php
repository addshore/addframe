<?php

/**
 * Class SiteFactory
 * Creates sites and adds them to the site registry
 * @deprecated
 */
class SiteFactory extends Registry{

	/**
	 * @param $url string url pointing to the site
	 * @return Mediawiki
	 * @deprecated
	 */
	function addSite( $url ){
		$this->$url = new Mediawiki($url);;
		return $this->$url;
	}

	/**
	 * @param $url
	 * @return Mediawiki
	 * @deprecated
	 */
	function getSite( $url ){
		if(!isset($this->$url)){
			return $this->addSite($url);
		}
		return $this->$url;
	}

}