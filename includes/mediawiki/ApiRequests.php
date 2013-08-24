<?php

namespace Addframe\Mediawiki;

use Addframe\Mediawiki\ApiRequest;

/**
 * This file contains classes for all api functions in the core of mediawiki
 */

/**
 * Class QueryRequest action=query
 */
class QueryRequest extends ApiRequest{
	function __construct( $params = array(), $shouldBePosted = false, $maxAge = CACHE_NONE ){
		parent::__construct( array_merge( array( 'action' => 'query' ), $params ), $shouldBePosted, $maxAge );
	}
}

/**
 * Class SiteInfoRequest meta=siteinfo
 */
class SiteInfoRequest extends QueryRequest{
	function __construct(
		$siprop = 'general',
		$sifilteriw = null,
		$sishowalldb = null,
		$sinumberingroup = null,
		$siinlanguagecode = null
	) {
		parent::__construct( array(
			'meta' => 'siteinfo',
			'siprop' => $siprop,
			'sifilteriw' => $sifilteriw,
			'sishowalldb' => $sishowalldb,
			'sinumberingroup' => $sinumberingroup,
			'siinlanguagecode' => $siinlanguagecode,
		), false, CACHE_WEEK );
	}
}

/**
 * Class LoginRequest action=login
 */
class LoginRequest extends ApiRequest{
	function __construct( $lgname = null, $lgpassword = null, $lgtoken = null, $lgdomain = null ) {
		parent::__construct( array(
			'action' => 'login',
			'lgname' => $lgname,
			'lgpassword' => $lgpassword,
			'lgdomain' => $lgdomain,
			'lgtoken' => $lgtoken,
		), true, CACHE_NONE );
	}
}

/**
 * Class LogoutRequest action=logout
 */
class LogoutRequest extends ApiRequest{
	function __construct( ) {
		parent::__construct( array(
			'action' => 'logout',
		), false, CACHE_NONE );
	}
}

/**
 * Class TokensRequest action=tokens
 */
class TokensRequest extends ApiRequest{
	function __construct( $type = 'edit' ) {
		parent::__construct( array(
			'action' => 'tokens',
			'type' => $type,
		), false, CACHE_HOUR );
	}
}
/**
 * Class EditRequest action=edit
 */
class EditRequest extends ApiRequest{
	function __construct(
		$title = null,
		$pageid = null,
		$section = null,
		$sectiontitle = null,
		$text = null,
		$token = null,
		$summary = null,
		$minor = null,
		$notminor = null,
		$bot = null,
		$basetimestamp = null,
		$starttimestamp = null,
		$recreate = null,
		$createonly = null,
		$nocreate = null,
		$watch = null,
		$unwatch = null,
		$watchlist = null,
		$md5 = null,
		$prependtext = null,
		$appendtext = null,
		$undo = null,
		$undoafter = null,
		$redirect = null,
		$contentformat = null,
		$contentmodel = null
	) {
		parent::__construct( array(
			'title' => $title,
			'pageid' => $pageid,
			'section' => $section,
			'sectiontitle' => $sectiontitle,
			'text' => $text,
			'token' => $token,
			'summary' => $summary,
			'minor' => $minor,
			'notminor' => $notminor,
			'bot' => $bot,
			'basetimestamp' => $basetimestamp,
			'starttimestamp' => $starttimestamp,
			'recreate' => $recreate,
			'createonly' => $createonly,
			'nocreate' => $nocreate,
			'watch' => $watch,
			'unwatch' => $unwatch,
			'watchlist' => $watchlist,
			'md5' => $md5,
			'prependtext' => $prependtext,
			'appendtext' => $appendtext,
			'undo' => $undo,
			'undoafter' => $undoafter,
			'redirect' => $redirect,
			'contentformat' => $contentformat,
			'contentmodel' => $contentmodel
		), true, CACHE_NONE );
	}
}