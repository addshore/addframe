<?php

namespace Addframe\Mediawiki\Api;

/**
 * Class EditRequest action=edit
 */
class EditRequest extends Request{

	public function __construct( $params = array(), $shouldPost = true, $maxAge = CACHE_NONE ) {

		$this->addAllowedParams(
			array( 'action', 'title', 'pageid', 'section', 'sectiontitle', 'text', 'token', 'summary', 'minor',
				'notminor', 'bot', 'basetimestamp', 'starttimestamp', 'recreate', 'createonly', 'nocreate', 'watch',
				'unwatch', 'watchlist', 'md5', 'prependtext', 'appendtext', 'undo', 'undoafter', 'redirect',
				'contentformat', 'contentmodel' ) );

		$this->addParams( array( 'action' => 'edit' ) );

		if( array_key_exists( 'text', $params ) && !is_null( $params['text'] ) ){

			$params['md5'] = md5( $params['text'] );

		} else if( array_key_exists( 'prependtext', $params )
			&& array_key_exists( 'appendtext', $params )
			&& !is_null( $params['prependtext'] )
			&& !is_null( $params['appendtext'] ) ) {

			/**
			 * todo, see if the below handling is correct..
			 * The MD5 hash of the text parameter, or the prependtext and appendtext parameters concatenated.
			 * todo this could mean even if one is empty the md5 can be used with the concatenated strings
			 */
			$params['md5'] = md5( $params['prependtext'] . $params['appendtext'] );
		}

		parent::__construct( $params, $shouldPost, $maxAge );
	}
}