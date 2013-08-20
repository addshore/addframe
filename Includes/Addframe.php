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

	/**
	 * Log stuff to the logger.
	 * By default everything is printed unless the level is DEBUG,
	 * but that can be overriden by setting $print to true.
	 * @param string $msg
	 * @param int $severity
	 * @param bool $print
	 */
	public static function log( $msg, $severity = \KLogger::INFO, $print = false ) {
		Addframe::getLogger()->log( $msg, $severity, \KLogger::NO_ARGUMENTS );
		if ( $print || $severity != \KLogger::DEBUG ) {
			echo $msg;
		}
	}

}