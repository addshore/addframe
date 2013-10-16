<?php

namespace Addframe\Mediawiki;

use Addframe\Logger;
use Addframe\Mediawiki\Api\RevisionsRequest;

/**
 * Representation of a page version.
 * @TODO test all logic in here
 */
class Revision {

	/** @var Page */
	protected $page;
	/** @var bool|null */
	protected $missing;
	/** @var int */
	protected $revid;
	/** @var int */
	protected $parentid;
	/** @var bool */
	protected $minor;
	/** @var string */
	protected $user;
	/** @var int */
	protected $userid;
	/** @var string */
	protected $timestamp;
	/** @var int */
	protected $size;
	/** @var string */
	protected $sha1;
	/** @var string */
	protected $contentmodel;
	/** @var string */
	protected $comment;
	/** @var string */
	protected $parsedcomment;
	/** @var array */
	protected $tags;
	/** @var string */
	protected $contentformat;
	/** @var string */
	protected $content;

	public function load() {
		$request = new RevisionsRequest( );

		//todo also use pageid for request
		if( is_string( $this->page->getTitle() ) ){
			$request->setParameter( 'titles', $this->page );
		}
		if( is_int( $this->revid ) ){
			$request->setParameter( 'revids', $this->revid );
		}

		$result = $this->page->getSite()->getApi()->doRequest( $request );
		$result = array_shift( $result['query']['pages'] );

		if( array_key_exists( 'missing', $result ) ){
			$this->missing = true;
		} else {
			$this->missing = false;
			$this->revid = $result['revid'];
			$this->parentid = $result['parentid'];
			$this->user = $result['user'];
			$this->userid = $result['userid'];
			$this->timestamp = $result['timestamp'];
			$this->size = $result['size'];
			$this->sha1 = $result['sha1'];
			$this->contentmodel = $result['contentmodel'];
			$this->comment = $result['comment'];
			$this->parsedcomment = $result['parsedcomment'];
			$this->tags = $result['tags'];
			$this->contentformat = $result['contentformat'];
			$this->content = $result['content'];
		}
		//ignore these as the Page already has them
		//$this->ns = $result['ns'];
		//$this->title = $result['title'];

		return true;
	}

	/**
	 * @return int revisionid
	 */
	public function getRevId(){
		if( !is_int( $this->revid ) ){
			$this->load();
		}
		return $this->revid;
	}

	/**
	 * @param Page $page
	 * @return Revision
	 */
	public static function newFromPage( Page $page ){
		$revision = new Revision();
		$revision->page = $page;
		return $revision;
	}

	/**
	 * @param int $revid
	 * @return Revision
	 */
	public static function newFromRevId( $revid ){
		$revision = new Revision();
		$revision->revid = $revid;
		return $revision;
	}

}