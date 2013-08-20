<?php

namespace Addframe;
use KLogger;

/**
 * @author Addshore
 * @since 0.0.4
 **/

class Addframe {

	/**
	 * Logging class if enabled in config.
	 * Lazily enabled.
	 * @var KLogger
	 */
	static protected $logger;

	/**
	 * Sets up the logger for us to use
	 * @return KLogger
	 * @todo Allow the user to set the path
	 */
	public static function getLogger() {
		if ( self::$logger === null ) {
			self::$logger = new KLogger('addwiki.log', 6);
		}

		return self::$logger;
	}

}