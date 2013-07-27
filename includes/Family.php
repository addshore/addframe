<?php
/**
 * Class Family for a collection of  mediawiki sites
 */
class Family extends Registry {

	/**
	 * @var UserLogin
	 */
	private $login;

	/**
	 * @param UserLogin $login
	 */
	public function setLogin( $login ) {
		$this->login = $login;
	}

	/**
	 * @return UserLogin
	 */
	public function getLogin() {
		return $this->login;
	}

	/**
	 * @var array of sites with the following keys
	 * [url][wikiid][code][sitename][closed][private]
	 */
	private $sitematrix;

	/**
	 * @var Site[] List of sites in the family
	 */
	private $sites;

	/**
	 * Create an object to hold a family of sites
	 * If a home site is listed try to get the sitematrix
	 *
	 * @param $familyName
	 * @param null $globalLogin
	 * @param null $homeUrl
	 */
	function __construct( $familyName, $globalLogin = null, $homeUrl = null ) {
		if ( isset( $homeUrl ) ) {
			$this->addSite( $homeUrl );
			$this->sitematrix = $this->sites[$homeUrl]->requestSitematrix();
		}
		if ( isset( $globalLogin ) ) {
			$this->login = $globalLogin;
		}
	}

	/**
	 * @param $siteid string of the siteid for the site
	 * @return Site
	 */
	function getSiteFromSiteid( $siteid ) {
		if ( isset( $this->sitematrix[$siteid] ) ) {
			$url = parse_url( $this->sitematrix[$siteid]['url'] );
			$url = $url['host'];

			if ( ! isset( $this->sites[$url] ) ) {
				echo "Loading $url\n";
				$this->addSite( $url );
			}
			return $this->getSite( $url );
		}
	}

	/**
	 * @param $url
	 * @return Site
	 */
	function getSiteFromUrl( $url ) {
		if ( ! isset( $this->sites[$url] ) ) {
			$this->addSite( $url );
		}
		return $this->getSite( $url );
	}

	/**
	 * @param $url string of the site that we want to add to the family
	 * @return Site
	 */
	function addSite( $url ) {
		$this->sites[$url] = new Site( $url, $this );
		if ( isset( $this->login ) ) {
			$this->sites[$url]->setLogin( $this->login );
		}
		return $this->sites[$url];
	}

	/**
	 * @param $url
	 * @return Site
	 */
	function getSite( $url ) {
		return $this->sites[$url];
	}

}