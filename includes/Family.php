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
	private $sitematrix;
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
			$this->addSite( $homeUrl );
			$this->homeSite = $this->getSite( $homeUrl );
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
	public function getSitematrix() {
		if ( $this->sitematrix == null ){
			$this->sitematrix = $this->homeSite->requestSitematrix();
		}
		return $this->sitematrix;
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
		$sitematrix = $this->getSitematrix();
		if ( isset( $sitematrix[$siteid] ) ) {
			$url = parse_url( $sitematrix[$siteid]['url'] );
			$url = $url['host'];

			if ( ! isset( $this->sites[$url] ) ) {
				echo "Loading $url\n";
				$this->addSite( $url );
			}
			return $this->getSite( $url );
		}
	}

	/**
	 * @param $url string of the site that we want to add to the family
	 * @return Site
	 */
	public function addSite( $url ) {
		$this->sites[$url] = new Site( $url, $this );
		if ( isset( $this->login ) ) {
			$this->sites[$url]->setLogin( $this->login );
		}
		return $this->sites[$url];
	}

}