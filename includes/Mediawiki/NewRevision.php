<?php

namespace Addframe\Mediawiki;

/**
 * Representation of a page version that is new i.e. not yet saved
 * We force everything to be public in here so it can be edited!
 */
class NewRevision extends Revision {

	/** @var Page */
	public $page;
	/** @var bool|null */
	public $missing;
	/** @var int */
	public $revid;
	/** @var int */
	public $parentid;
	/** @var bool */
	public $minor;
	/** @var string */
	public $user;
	/** @var int */
	public $userid;
	/** @var string */
	public $timestamp;
	/** @var int */
	public $size;
	/** @var string */
	public $sha1;
	/** @var string */
	public $contentmodel;
	/** @var string */
	public $comment;
	/** @var string */
	public $parsedcomment;
	/** @var array */
	public $tags;
	/** @var string */
	public $contentformat;
	/** @var string */
	public $content;
	
	public function isMissing(){
		return true;
	}

}