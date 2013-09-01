<?php

namespace Addframe\Mediawiki;

use Addframe\Logger;

class Page {

	/**
	 * @var string page title
	 * todo this should be an object
	 */
	protected $title;
	/**
	 * @var Site of the user
	 */
	protected $site;
	/**
	 * @var int
	 */
	protected $ns;
	/**
	 * @var int pageid
	 */
	protected $id;
	/**
	 * @var string
	 */
	protected $contentmodel;
	/**
	 * @var string
	 */
	protected $pagelanguage;
	/**
	 * @var string date the page was last touched
	 */
	protected $touched;
	/**
	 * @var int
	 */
	protected $lastrevid;
	/**
	 * @var int //todo work out what this is...
	 */
	protected $counter;
	/**
	 * @var int
	 */
	protected $length;
	/**
	 * @var array
	 */
	protected $protection;
	/**
	 * @var string
	 */
	protected $displaytitle;

	/**
	 * @return string
	 */
	public function getTitle() {
		return $this->title;
	}

	/**
	 * @return int
	 */
	public function getId() {
		if( !isset( $this->id ) ){
			$this->load();
		}
		return $this->id;
	}

	/**
	 * @param $site Site
	 * @throws \UnexpectedValueException
	 */
	public function setSite( $site ) {
		if( !$site instanceof Site ){
			throw new \UnexpectedValueException( 'User site can only be set to an instance of site' );
		}
		$this->site = $site;
	}

	/**
	 * @throws \Exception
	 * @return Site
	 */
	public function getSite(){
		if( !$this->site instanceof Site ){
			throw new \UnexpectedValueException( 'User must have a site before the site can be used' );
		}
		return $this->site;
	}

	/**
	 * @return string
	 */
	public function getContentmodel() {
		if( !isset( $this->contentmodel ) ){
			$this->load();
		}
		return $this->contentmodel;
	}

	/**
	 * @return int
	 */
	public function getCounter() {
		if( !isset( $this->counter ) ){
			$this->load();
		}
		return $this->counter;
	}

	/**
	 * @return string
	 */
	public function getDisplaytitle() {
		if( !isset( $this->displaytitle ) ){
			$this->load();
		}
		return $this->displaytitle;
	}

	/**
	 * @return int
	 */
	public function getLastrevid() {
		if( !isset( $this->lastrevid ) ){
			$this->load();
		}
		return $this->lastrevid;
	}

	/**
	 * @return int
	 */
	public function getLength() {
		if( !isset( $this->length ) ){
			$this->load();
		}
		return $this->length;
	}

	/**
	 * @return string
	 */
	public function getPagelanguage() {
		if( !isset( $this->pagelanguage ) ){
			$this->load();
		}
		return $this->pagelanguage;
	}

	/**
	 * @return array
	 */
	public function getProtection() {
		if( !isset( $this->protection ) ){
			$this->load();
		}
		return $this->protection;
	}

	/**
	 * @return string
	 */
	public function getTouched() {
		if( !isset( $this->touched ) ){
			$this->load();
		}
		return $this->touched;
	}

	/**
	 * @return int
	 */
	public function getNs() {
		if( !isset( $this->ns ) ){
			$this->load();
		}
		return $this->ns;
	}

	/**
	 * Load the page information
	 */
	public function load() {
		try{
			$request = new Api\InfoRequest( array( 'titles' => $this->getTitle() ) );
			$result = $this->getSite()->getApi()->doRequest( $request );
			$result = array_shift( $result['query']['pages'] );
			//todo factor the below out into setFromArray()??
			$this->id = $result['pageid'];
			$this->ns = $result['ns'];
			$this->title = $result['title'];
			$this->contentmodel = $result['contentmodel'];
			$this->pagelanguage = $result['pagelanguage'];
			$this->touched = $result['touched'];
			$this->lastrevid = $result['lastrevid'];
			$this->counter = $result['counter'];
			$this->length = $result['length'];
			$this->protection = $result['protection'];
			//todo work out if the below commented out values are needed for anything...
//			$this->notificationtimestamp = $result['notificationtimestamp'];
//			$this->fullurl = $result['fullurl'];
//			$this->editurl = $result['editurl'];
//			$this->readable = $result['readable'];
//			$this->preload = $result['preload'];
			$this->displaytitle = $result['displaytitle'];
			return true;
		} catch ( \UnexpectedValueException $e ){
			Logger::logError( "Cannot load page details for {$this->title} as no Site is defined" );
			return false;
		}
	}

	/**
	 * @param $title string of page title
	 * @param Site $site
	 * @return Page
	 * todo title should be a class
	 */
	public static function newFromTitle( $title, $site = null ){
		$page = new Page( );
		$page->title = $title;
		if( !is_null( $site ) ){
			$page->setSite( $site );
		}
		return $page;
	}

}