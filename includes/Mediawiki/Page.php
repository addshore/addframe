<?php

namespace Addframe\Mediawiki;

use Addframe\Logger;
use Addframe\Mediawiki\Api\InfoRequest;
use UnexpectedValueException;

class Page {

	//todo this should be an object
	/** @var string page title */
	protected $title;
	/** @var Site of the user */
	protected $site;
	/** @var int */
	protected $ns;
	/** @var int pageid */
	protected $id;
	/** @var bool|null is the page missing? */
	protected $missing;
	/** @var string */
	protected $contentmodel;
	/** @var string */
	protected $pagelanguage;
	/** @var string date the page was last touched */
	protected $touched;
	/** @var int */
	protected $lastrevid;
	/** @var int //todo work out what this is... */
	protected $counter;
	/** @var int */
	protected $length;
	/** @var array */
	protected $protection;
	/** @var string */
	protected $displaytitle;
	/** @var array */
	protected $revisions = array();

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
			$this->loadInfo();
		}
		return $this->id;
	}

	/**
	 * @param $site Site
	 * @throws UnexpectedValueException
	 */
	public function setSite( $site ) {
		if( !$site instanceof Site ){
			throw new UnexpectedValueException( 'User site can only be set to an instance of site' );
		}
		$this->site = $site;
	}

	/**
	 * @throws UnexpectedValueException
	 * @return Site
	 */
	public function getSite(){
		if( !$this->site instanceof Site ){
			throw new UnexpectedValueException( 'User must have a site before the site can be used' );
		}
		return $this->site;
	}

	public function isMissing(){
		//todo unit test
		if( !is_bool( $this->missing ) ){
			$this->loadInfo();
		}
		return $this->missing;
	}

	/**
	 * @return string
	 */
	public function getContentmodel() {
		if( !isset( $this->contentmodel ) ){
			$this->loadInfo();
		}
		return $this->contentmodel;
	}

	/**
	 * @return int
	 */
	public function getCounter() {
		if( !isset( $this->counter ) ){
			$this->loadInfo();
		}
		return $this->counter;
	}

	/**
	 * @return string
	 */
	public function getDisplaytitle() {
		if( !isset( $this->displaytitle ) ){
			$this->loadInfo();
		}
		return $this->displaytitle;
	}

	/**
	 * @return int
	 */
	public function getLastrevid() {
		if( !isset( $this->lastrevid ) ){
			$this->loadInfo();
		}
		return $this->lastrevid;
	}

	/**
	 * @return int
	 */
	public function getLength() {
		if( !isset( $this->length ) ){
			$this->loadInfo();
		}
		return $this->length;
	}

	/**
	 * @return string
	 */
	public function getPagelanguage() {
		if( !isset( $this->pagelanguage ) ){
			$this->loadInfo();
		}
		return $this->pagelanguage;
	}

	/**
	 * @return array
	 */
	public function getProtection() {
		if( !isset( $this->protection ) ){
			$this->loadInfo();
		}
		return $this->protection;
	}

	/**
	 * @return string
	 */
	public function getTouched() {
		if( !isset( $this->touched ) ){
			$this->loadInfo();
		}
		return $this->touched;
	}

	/**
	 * @return int
	 */
	public function getNs() {
		if( !isset( $this->ns ) ){
			$this->loadInfo();
		}
		return $this->ns;
	}

	public function load(){
		$this->loadInfo();
		$this->loadRevision();
	}

	/**
	 * Load the page information
	 */
	public function loadInfo() {
		try{
			$request = new InfoRequest( array( 'titles' => $this->getTitle() ) );
			$result = $this->getSite()->getApi()->doRequest( $request );
			$result = array_shift( $result['query']['pages'] );

			if( array_key_exists( 'missing', $result ) ){
				$this->missing = true;
			} else {
				$this->missing = false;
				$this->id = $result['pageid'];
				$this->touched = $result['touched'];
				$this->lastrevid = $result['lastrevid'];
				$this->counter = $result['counter'];
				$this->length = $result['length'];
			}
			$this->ns = $result['ns'];
			$this->title = $result['title'];
			$this->contentmodel = $result['contentmodel'];
			$this->pagelanguage = $result['pagelanguage'];
			$this->protection = $result['protection'];
			$this->displaytitle = $result['displaytitle'];
			//todo work out if the below commented out values are needed for anything...
//			$this->notificationtimestamp = $result['notificationtimestamp'];
//			$this->fullurl = $result['fullurl'];
//			$this->editurl = $result['editurl'];
//			$this->readable = $result['readable'];
//			$this->preload = $result['preload'];

			return true;
		} catch ( UnexpectedValueException $e ){
			Logger::logError( "Cannot load page details for {$this->title} as no Site is defined" );
			return false;
		}
	}

	//todo test
	public function getCurrentRevision(){
		return $this->getRevision( null );
	}

	/**
	 * @param int|null $revid A revid OR null to get the most recent revision
	 * @return Revision
	 * @todo test
	 */
	public function getRevision( $revid = null ){
		if( $revid === null ){
			$revid = $this->getLastrevid();
		}

		if( !array_key_exists( $revid, $this->revisions ) ){
			$revision = Revision::newFromRevId( $revid );
			$this->addRevision( $revision );
		}
		return $this->revisions[$revid];
	}

	/**
	 * @param null|Revision $revision If no revision is set we will get the most recent
	 * @return Revision
	 * @todo test
	 */
	public function loadRevision( $revision = null ){
		if( $revision === null ){
			$revision = Revision::newFromPage( $this );
		}
		$revision->load();
		$this->addRevision( $revision );
		return $revision;
	}

	//todo test
	public function addRevision( Revision $revision ){
		$this->revisions[$revision->getRevId()] = $revision;
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