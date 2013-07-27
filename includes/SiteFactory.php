<?php

/**
 * Class SiteFactory
 * Creates sites and adds them to the site registry
 * @deprecated
 */
class SiteFactory extends Registry {

	/**
	 * @param $url string url pointing to the site
	 * @return Site
	 * @deprecated
	 */
	public function addSite( $url ) {
		$this->$url = new Site( $url );;
		return $this->$url;
	}

	/**
	 * @param $url
	 * @return Site
	 * @deprecated
	 */
	public function getSite( $url ) {
		if ( ! isset( $this->$url ) ) {
			return $this->addSite( $url );
		}
		return $this->$url;
	}

}