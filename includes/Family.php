<?php
/**
 * Class Family for a collection of  mediawiki sites
 */
class Family extends Registry {

	public $login;
	/**
	 * @var array of sites with the following keys
	 * [url][wikiid][code][sitename][closed][private]
	 */
	private $sitematrix;

	/**
	 * @var Mediawiki[]
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
		if( isset( $homeUrl ) ){
			$this->addSite( $homeUrl );
			$this->sitematrix = $this->sites[$homeUrl]->getSitematrix();
		}
		if( isset( $globalLogin ) ){
			$this->login = $globalLogin;
		}
	}

	/**
	 * @param $siteid string of the siteid for the site
	 * @return Mediawiki
	 */
	function getFromSiteid( $siteid ){
		if( isset( $this->sitematrix[$siteid] ) ){
			$url = parse_url ( $this->sitematrix[$siteid]['url'] );
			$url = $url['host'];

			if( !isset( $this->sites[$url] ) ){
				echo "Loading $url\n";
				$this->addSite( $url );
			}
			return $this->getSite( $url );
		}
	}

	function getFromUrl(){

	}

	function addSite( $url ){
		$this->sites[$url] = new Mediawiki( $url, $this );
		if( isset( $this->login ) ) {
			$this->sites[$url]->setLogin( $this->login );
		}
		return $this->sites[$url];
	}

	/**
	 * @param $url
	 * @return Mediawiki
	 */
	function getSite( $url ){
		return $this->sites[$url];
	}

}