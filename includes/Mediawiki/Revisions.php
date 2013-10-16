<?php

namespace Addframe\Mediawiki;

use UnexpectedValueException;

class Revisions {

	/** @var Revision[] */
	protected $revisions;

	/**
	 * @param null|Revisions[]|Revision $data
	 *
	 * @throws UnexpectedValueException
	 */
	function __construct( $data = null ) {
		$this->revisions = array();
		if( is_array( $data ) ){
			foreach( $data as $rev ){
				if( $rev instanceof Revision ){
					$this->add( $rev );
				} else {
					throw new UnexpectedValueException( 'Array should contain only instances of Revision' );
				}
			}
		} else if ( $data instanceof Revision ){
			$this->add( $data );
		} else if ( !$data === null ) {
			throw new UnexpectedValueException( 'Revisions should be constructed with a Revision of array of Revisions' );
		}
	}

	public function hasRevisionWithId( $id ){
		if( array_key_exists( $id, $this->revisions ) ){
			return true;
		} else {
			return false;
		}
	}

	public function hasRevision( Revision $revision ){
		return $this->hasRevisionWithId( $revision->getRevId() );
	}

	public function count(){
		return count($this->revisions);
	}

	/**
	 * @param $revid
	 * @return Revision|null
	 */
	public function get( $revid ){
		if( $this->hasRevisionWithId( $revid ) ){
			return $this->revisions[$revid];
		}
		return null;
	}

	public function add( Revision $revision ){
		//todo throw exception is no revid is set..
		$this->revisions[ $revision->getRevId() ] = $revision;
	}

}