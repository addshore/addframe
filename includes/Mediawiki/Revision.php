<?php

namespace Addframe\Mediawiki;

use Addframe\Logger;

/**
 * Representation of a page version.
 */
abstract class Revision {

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

	/**
	 * @return int revisionid
	 */
	public function getRevId(){
		return $this->revid;
	}

	public function getContent() {
		return $this->content;
	}

	public function getComment() {
		return $this->comment;
	}

	public function isMissing(){
		return $this->missing;
	}

	public function isMinor(){
		return $this->minor;
	}

	/**
	 * @param Page $page
	 * @return NewRevision
	 */
	public static function newFromPage( Page $page ){
		$revision = new NewRevision();
		$revision->page = $page;
		return $revision;
	}

	/**
	 * @param int $revid
	 * @param null|Page $page
	 * @return Revision
	 */
	public static function newFromRevId( $revid, $page = null ){
		$revision = new SavedRevision();
		$revision->revid = $revid;
		if( $page !== null ){
			$revision->page = $page ;
		}
		return $revision;
	}

	/**
	 * @param Revision $revision
	 * @return NewRevision
	 */
	public static function newBasedOnRevision( Revision $revision ){
		$newRevision = new NewRevision();
		$newRevision->page = $revision->page;
		$newRevision->parentid = $revision->revid;
		//todo set the other things that will be the same such as content?
		return $newRevision;
	}

}