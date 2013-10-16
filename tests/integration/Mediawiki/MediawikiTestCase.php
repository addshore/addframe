<?php

namespace Addframe\Test\Integration;

use Addframe\Mediawiki\Site;

class MediawikiTestCase extends \PHPUnit_Framework_TestCase {

	/** @var Site|null */
	protected $site = null;
	/** @var bool */
	protected $siteIsLoggedIn = false;

	/**
	 * @param bool $logIn
	 * @return Site
	 */
	public function newSite( $logIn = true ) {
		if( !$this->site instanceof Site ){
			$this->site = Site::newFromUrl( SITEURL );
		}
		if( $logIn && !$this->siteIsLoggedIn ){
			$this->site->login( SITEUSER, SITEPASS );
			$this->siteIsLoggedIn = true;
		} else if( !$logIn || $this->siteIsLoggedIn ) {
			$this->site->logout();
			$this->siteIsLoggedIn = false;
		}
		return $this->site;
	}

}