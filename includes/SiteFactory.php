<?php

/**
 * Class SiteFactory
 * Creates sites and adds them to the site registry
 */
class SiteFactory extends Registry{

	/**
	 * @param $handel string of site
	 * @param $api string url pointing to api
	 * @return Mediawiki
	 */
	function addSite( $handel, $api){
		$this->$handel = new Mediawiki($handel,$api);
		return $this->$handel;
	}

	/**
	 * @param $handel
	 * @return Mediawiki
	 */
	function getSite( $handel ){
		return $this->$handel;
	}
}