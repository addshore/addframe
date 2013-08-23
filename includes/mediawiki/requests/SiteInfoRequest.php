<?php


namespace Addframe\Mediawiki;


class SiteInfoRequest extends ApiRequest{

	public function isCacheable(){
		return true;
	}

	protected function getAllowedParams() {
		return array_merge(
			parent::getAllowedParams(),
			array( 'siprop', 'sifilteriw', 'sishowalldb', 'sinumberingroup', 'siinlanguagecode' )
		);
	}

}