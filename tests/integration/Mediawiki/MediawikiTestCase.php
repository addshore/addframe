<?php

namespace Addframe\Test\Integration;

use Addframe\Mediawiki\Site;

class MediawikiTestCase extends \PHPUnit_Framework_TestCase {

	protected $site;
	protected $siteIsLoggedIn = false;

	public function newSite( $logIn = true ) {
		if( !$this->site instanceof Site ){
			$this->site = Site::newFromUrl( SITEURL );
		}
		if( $logIn && !$this->siteIsLoggedIn ){
			$this->site->login( SITEUSER, SITEPASS );
		} else if( $this->siteIsLoggedIn ) {
			$this->site->logout();
		}
		return $this->site;
	}

}