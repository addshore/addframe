<?php
/**
 * Class Family for a collection of  mediawiki sites
 */
class Family {

	public $login;
	private $siteFactory;
	/**
	 * @var array of sites with the following keys
	 * [url][wikiid][code][sitename][closed][private]
	 */
	private $sitematrix;

	/**
	 * Create an object to hold a family of sites
	 * If a home site is listed try to get the sitematrix
	 *
	 * @param $familyName
	 * @param null $globalLogin
	 * @param null $homrUrl
	 */
	function __construct( $familyName, $globalLogin = null, $homrUrl = null ) {
		$this->siteFactory = new SiteFactory();
		if(isset($homrUrl)){
			$homeSite = Globals::$Sites->addSite($homrUrl);
			$this->sitematrix = $homeSite->getSitematrix();
		}
		if(isset($globalLogin)){
			$this->login = $globalLogin;
		}
	}

	/**
	 * @param $siteid string of the siteid for the site
	 * @return Mediawiki
	 */
	function getFromSiteid($siteid){
		if( isset($this->sitematrix[$siteid]) ){
			$url = parse_url ( $this->sitematrix[$siteid]['url'] );
			$url = $url['host'];

			if( !isset(Globals::$Sites->objects[$url]) ){
				echo "Adding $url to registry of sites\n";
				$this->addSite($url);
			}
			return $this->getSite($url);
		}
	}

	function addSite($url){
		$site = Globals::$Sites->addSite($url);
		if(isset($this->login)){
			Globals::$Sites->getSite($url)->setLogin($this->login);
		}
		return $site;
	}

	/**
	 * @param $url
	 * @return Mediawiki
	 */
	function getSite($url){

		return Globals::$Sites->getSite($url);
	}

}