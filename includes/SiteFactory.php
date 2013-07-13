<?php

/**
 * Class SiteFactory
 * Creates sites and adds them to the site registry
 */
class SiteFactory extends Registry{

	/**
	 * @param $handel string of site
	 * @param null $hostname
	 * @param null $api
	 * @return Mediawiki
	 */
	function newSite( $handel, $hostname = null, $api = null){
		if(isset($hostname) && isset($api)){
			$this->$handel = new Mediawiki($handel,$hostname,$api);
		}
		else{
			//@todo lookup site from a sitematrix kind of thing
		}
		return $this->$handel;
	}

	/**
	 * @param $handel
	 * @return Mediawiki
	 */
	function getSite( $handel ){
		if( isset($this->$handel) ){
			return $this->$handel;
		} else{
			//return site from config
		}

	}
}