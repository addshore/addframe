<?php

namespace Addframe\Mediawiki;

use Addframe\Mediawiki\Api\RevisionsRequest;
use Exception;
use LogicException;

/**
 * Representation of a page version that is Saved i.e. we can not edit this
 * If something is not set in here we try to load it first
 * Nothing in here can be set by the user
 */
class SavedRevision extends Revision {

	/**
	 * @return int revisionid
	 */
	public function getRevId(){
		if( $this->revid === null ){
			$this->load();
		}
		return $this->revid;
	}

	public function getContent() {
		if( $this->content === null ){
			$this->load();
		}
		return $this->content;
	}

	public function getComment() {
		if( $this->comment === null ){
			$this->load();
		}
		return $this->comment;
	}

	public function isMissing(){
		if( $this->missing  === null ){
			$this->load();
		}
		return $this->missing;
	}

	public function isMinor(){
		if( $this->minor === null ){
			$this->load();
		}
		return $this->minor;
	}

	public function load() {
		$request = new RevisionsRequest();

		if( $this->revid !== null ){
			$request->setParameter( 'revids', $this->revid );
		} else if( $this->page instanceof Page && $this->page->getTitle() !== null ){
			$request->setParameter( 'titles', $this->page->getTitle() );
		} else if( $this->page instanceof Page && $this->page->getId() !== null ){
			$request->setParameter( 'pageids', $this->page->getId() );
		} else {
			throw new Exception( 'No way to identify the revision to load' );
		}

		$result = $this->page->getSite()->getApi()->doRequest( $request );
		$result = array_shift( $result['query']['pages'] );

		if( array_key_exists( 'missing', $result ) ){
			$this->missing = true;
		} else {
			$result = array_shift( $result['revisions'] );
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
			$this->content = $result['*'];
		}
		//ignore these as the Page already has them
		//$this->ns = $result['ns'];
		//$this->title = $result['title'];

		return true;
	}

	public function undo(){
		throw new LogicException( __METHOD__ . ' Not yet implemented' );
	}

	public function restore(){
		throw new LogicException( __METHOD__ . ' Not yet implemented' );
	}

}