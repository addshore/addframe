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
	 * @param $dbname string of the dbname for the site
	 * @return Mediawiki
	 */
	function getFromMatrix($dbname){
		if( isset($this->sitematrix[$dbname]) && !isset(Globals::$Sites->objects[$dbname]) ){
			//@todo need a better way to know where the api is
			echo "Adding $dbname to registry of sites\n";
			$this->addSite($this->sitematrix[$dbname]['url']);
		}
		return $this->getSite($dbname);
	}

	function addSite($url){
		$site = Globals::$Sites->addSite($url);
		if(isset($this->login)){
			Globals::$Sites->getSite($site->wikiid)->setLogin($this->login);
		}
		return $site;
	}

	/**
	 * @param $handle
	 * @return Mediawiki
	 */
	function getSite($handle){

		return Globals::$Sites->getSite($handle);
	}

}