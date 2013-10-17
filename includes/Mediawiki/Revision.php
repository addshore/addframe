<?php

namespace Addframe\Mediawiki;

use Addframe\Logger;
use Addframe\Mediawiki\Api\RevisionsRequest;
use LogicException;

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

	public function undo(){
		throw new LogicException( __METHOD__ . ' Not yet implemented' );
	}

	public function restore(){
		throw new LogicException( __METHOD__ . ' Not yet implemented' );
	}

	public function load() {
		$request = new RevisionsRequest();

		//todo also use pageid for request
		if( $this->page instanceof Page && is_string( $this->page->getTitle() ) ){
			$request->setParameter( 'titles', $this->page->getTitle() );
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

	public function setRevId( $revid ) {
		$this->revid = $revid;
	}

	public function getContent() {
		if( !is_string( $this->content ) ){
			$this->load();
		}
		return $this->content;
	}

	public function getComment() {
		if( !is_string( $this->comment ) ){
			$this->load();
		}
		return $this->comment;
	}

	public function setContent( $content ){
		$this->content = $content;
	}

	public function setComment( $comment ){
		$this->comment = $comment;
	}

	public function isMissing(){
		if( !is_bool( $this->missing ) ){
			$this->load();
		}
		return $this->missing;
	}

	public function isMinor(){
		if( !is_bool( $this->minor ) ){
			$this->load();
		}
		return $this->minor;
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
	 * @param null|Page $page
	 * @return Revision
	 */
	public static function newFromRevId( $revid, $page = null ){
		$revision = new Revision();
		$revision->revid = $revid;
		if( !is_null( $page ) ){
			$revision->page = $page ;
		}
		return $revision;
	}

	public static function newFromRevision( Revision $revision ){
		$newRevision = new Revision();
		$newRevision->page = $revision->page;
		$newRevision->parentid = $revision->revid;
		//todo set the other things that will be the same such as content
		return $newRevision;
	}

}