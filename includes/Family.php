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
			Globals::$Sites->addSite($this->homeHandel, $homeApi);
			$this->sitematrix = Globals::$Sites->getSite($this->homeHandel)->getSitematrix();
		}
		if(isset($globalLogin)){
			$this->login = $globalLogin;
		}
	}

	/**
	 * @param $dbname
	 * @return Mediawiki
	 */
	function getFromMatrix($dbname){
		if( isset($this->sitematrix[$dbname]) && !isset(Globals::$Sites->objects[$dbname]) ){
			//@todo need a better way to know where the api is
			$this->addSite($this->sitematrix[$dbname]['dbname'],$this->sitematrix[$dbname]['url'].'/w/api.php');
		}
		return $this->getSite($dbname);
	}

	function addSite($handle, $api){
		Globals::$Sites->addSite($handle,$api);
		if(isset($this->login)){
			Globals::$Sites->getSite($handle)->setLogin($this->login);
		}
	}

	/**
	 * @param $handle
	 * @return Mediawiki
	 */
	function getSite($handle){

		return Globals::$Sites->getSite($handle);
	}

}