<?php

namespace Addframe\Mediawiki;

use Addframe\Logger;
use Addframe\Mediawiki\Api\UsersRequest;
use UnexpectedValueException;

class User {

	/**
	 * @var array of implicit groups
	 */
	protected $implicitgroups;
	/**
	 * @var array of user rights
	 */
	protected $rights;
	/**
	 * @var string gender (male|female|unknown)
	 */
	protected $gender;
	/**
	 * @var int userid
	 */
	protected $id;
	/**
	 * @var string username
	 */
	protected $name;
	/**
	 * @var string MediawikiTimestamp of the registration date
	 */
	protected $registration;
	/**
	 * @var int edit count
	 */
	protected $editcount;
	/**
	 * @var array of groups
	 */
	protected $groups;
	/**
	 * @var Site of the user
	 */
	protected $site;

	/**
	 * @return int
	 */
	public function getEditcount() {
		if( !isset( $this->editcount ) ){
			$this->load();
		}
		return $this->editcount;
	}

	/**
	 * @return array
	 */
	public function getGroups() {
		if( !isset( $this->groups ) ){
			$this->load();
		}
		return $this->groups;
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
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * @return string
	 */
	public function getRegistration() {
		if( !isset( $this->registration ) ){
			$this->load();
		}
		return $this->registration;
	}

	/**
	 * @return string
	 */
	public function getGender() {
		if( !isset( $this->gender ) ){
			$this->load();
		}
		return $this->gender;
	}

	/**
	 * @return array
	 */
	public function getImplicitgroups() {
		if( !isset( $this->implicitgroups ) ){
			$this->load();
		}
		return $this->implicitgroups;
	}

	/**
	 * @return array
	 */
	public function getRights() {
		if( !isset( $this->rights ) ){
			$this->load();
		}
		return $this->rights;
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
	 * @throws \Exception
	 * @return Site
	 */
	public function getSite(){
		if( !$this->site instanceof Site ){
			throw new UnexpectedValueException( 'User must have a site before the site can be used' );
		}
		return $this->site;
	}

	/**
	 * Loads user details from site
	 */
	public function load(){
		try{
			$request = new UsersRequest( array ( 'ususers' => $this->getName() ) );
			$result = $this->getSite()->getApi()->doRequest( $request );
			$result = $result['query']['users'][0];
			//todo factor the below out into setFromArray()??
			$this->id = $result['userid'];
			$this->name = $result['name'];
			$this->editcount = $result['editcount'];
			$this->registration = $result['registration'];
			$this->groups = $result['groups'];
			$this->implicitgroups = $result['implicitgroups'];
			$this->rights = $result['rights'];
			$this->gender = $result['gender'];
			return true;
		} catch ( UnexpectedValueException $e ){
			Logger::logError( "Cannot load user details for {$this->name} as no Site is defined" );
			return false;
		}
	}

	/**
	 * @return Page
	 */
	public function getUserPage(){
		//todo also set the namespace..
		return Page::newFromTitle( 'User:' . $this->name, $this->getSite() );
	}

	/**
	 * @return Page
	 */
	public function getUserTalkPage(){
		//todo also set the namespace..
		return Page::newFromTitle( 'User talk:' . $this->name, $this->getSite() );
	}

	/**
	 * @param $name string of the user
	 * @param Site $site
	 * @return User
	 */
	public static function newFromUsername( $name, $site = null ){
		$user = new User( );
		$user->name = $name;
		if( !is_null( $site ) ){
			$user->setSite( $site );
		}
		return $user;
	}

}