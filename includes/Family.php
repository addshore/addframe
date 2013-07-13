<?php
/**
 * Class Family for a collection of  mediawiki sites
 */
class Family {

	public $name;
	public $login;
	private $siteFactory;
	private $homeHandel;
	/**
	 * @var array of sites with the following keys
	 * [url][dbname][code][sitename][closed][private]
	 */
	private $sitematrix;

	/**
	 * Create an object to hold a family of sites
	 * If a home site is listed try to get the sitematrix
	 *
	 * @param $familyName
	 * @param null $globalLogin
	 * @param null $homeApi
	 */
	function __construct( $familyName, $globalLogin = null, $homeApi = null ) {
		$this->siteFactory = new SiteFactory();
		$this->name = $familyName;
		if(isset($homeApi)){
			$this->homeHandel = $familyName.'-homesite';
			$this->siteFactory->addSite($this->homeHandel, $homeApi);
			$this->sitematrix = $this->siteFactory->getSite($this->homeHandel)->getSitematrix();
		}
		if(isset($globalLogin)){
			$this->login = $globalLogin;
		}
	}

	/**
	 * @param $dbname
	 * @return Mediawiki
	 */
	function addSiteFromMatrix($dbname){
		if(isset($this->sitematrix[$dbname])){
			//@todo need a better way to know where the api is
			$this->addSite($this->sitematrix['dbname'],$this->sitematrix['url'].'/w/api.php');
		}
		return $this->getSite($dbname);
	}

	function addSite($handle, $api){
		$this->siteFactory->addSite($handle,$api);
		if(isset($this->login)){
			$this->siteFactory->getSite($handle)->setLogin($this->login);
		}
	}

	/**
	 * @param $handle
	 * @return Mediawiki
	 */
	function getSite($handle){
		return $this->siteFactory->getSite($handle);
	}

}