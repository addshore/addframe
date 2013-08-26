<?php

namespace Addframe;

/**
 * Finally, a light, permissions-checking logging class.
 *
 * Originally written for use with wpSearch
 *
 * @author  Kenny Katzgrau <katzgrau@gmail.com>
 * @since   July 26, 2008 — Last update July 1, 2012
 * @link    http://codefury.net
 * @version 0.2.0
 *
 * @author Addshore ( Modified for use with Addframe )
 */

class Logger {

	/**
	 * Error severity, from low to high. From BSD syslog RFC, secion 4.1.1
	 * @link http://www.faqs.org/rfcs/rfc3164.html
	 */
	const EMERG = 0; // Emergency: system is unusable
	const ALERT = 1; // Alert: action must be taken immediately
	const CRIT = 2; // Critical: critical conditions
	const FATAL = 2; // Alias for CRIT
	const ERR = 3; // Error: error conditions
	const WARN = 4; // Warning: warning conditions
	const NOTICE = 5; // Notice: normal but significant condition
	const INFO = 6; // Informational: informational messages
	const DEBUG = 7; // Debug: debug messages
	const OFF = 8; //custom logging level

	/**
	 * Has the class setup?
	 * @var bool
	 */
	private static $isSetup = false;
	/**
	 * Path to the logging directory
	 * @var string
	 */
	private static $logDirectory;
	/**
	 * Current minimum logging threshold
	 * @var integer
	 */
	private static $severityThresholds = array();
	/**
	 * This holds the file handle for this instance's log file
	 * @var resource
	 */
	private static $fileHandles = array();
	/**
	 * Destructor instance
	 * @var LoggerDestructor
	 */
	private static $destructorInstance;
	private static $defaultSeverityThreshold = Logger::INFO;

	/**
	 * Class init
	 */
	private static function setup() {
		if ( self::$isSetup === false ) {

			if ( null === self::$destructorInstance )
				self::$destructorInstance = new LoggerDestructor();

			self::$logDirectory = __DIR__ . '/../log';

			if ( ! file_exists( self::$logDirectory ) ) {
				mkdir( self::$logDirectory, 0777, true );
			}

			self::$isSetup = true;
		}

		//start our default log..
		self::setupLog();
	}

	/**
	 * Class destructor
	 */
	public static function _destruct() {
		foreach ( self::$fileHandles as $handle ) {
			fclose( $handle );
		}
		self::$fileHandles = array();
		self::$severityThresholds = array();
		self::$destructorInstance = null;
		self::$isSetup = false;
	}

	public static function setDefaultSeverityThreshold( $severity ){
		self::$defaultSeverityThreshold = $severity;
	}

	/**
	 * setupLog
	 *
	 * @param string $label Label of the log
	 * @param int $severity
	 * @throws \IOException
	 * @return Logger
	 */
	public static function setupLog( $label = 'log', $severity = null ) {
		if ( ! array_key_exists( $label, self::$fileHandles ) ) {

			if ( self::$isSetup === false ) {
				self::setup();
			}

			if( is_null( $severity ) ){
				$severity = self::$defaultSeverityThreshold;
			}

			if ( $label === 'log' ) {
				$logFilePath = self::$logDirectory . '/' . date( 'Y-m-d' ) . '.txt';
			} else {
				if ( ! file_exists( self::$logDirectory . '/' . $label ) ) {
					mkdir( self::$logDirectory . '/' . $label, 0777, true );
				}
				$logFilePath = self::$logDirectory . '/' . $label . '/' . date( 'Y-m-d' ) . '.txt';
			}

			if ( file_exists( $logFilePath ) && ! is_writable( $logFilePath ) ) {
				throw new \IOException( "Can not write to log path {$logFilePath}" );
			}

			if ( ( $fileHandle = fopen( $logFilePath, 'a' ) ) ) {
				self::$severityThresholds[$label] = $severity;
				self::$fileHandles[$label] = $fileHandle;
			} else {
				throw new \IOException( "Can not open log path {$logFilePath}" );
			}

		}
	}

	/**
	 * Writes a $line to the log with the given severity
	 *
	 * @param string $line     Text to add to the log
	 * @param integer $severity Severity level of log message (use constants)
	 * @param string $label
	 * @internal param string $args
	 */
	public static function log( $line, $severity = Logger::INFO, $label = 'log' ) {
		if ( self::$severityThresholds >= $severity ) {
			$status = self::getTimeLine( $severity );

			self::writeLine( "$status $line" . PHP_EOL, $label );
		}
	}

	public static function logDebug( $line, $label = 'log' ) {
		self::log( $line, self::DEBUG, $label );
	}

	public static function logInfo( $line, $label = 'log' ) {
		self::log( $line, self::INFO, $label );
	}

	/**
	 * Writes a $line to the log with a severity level of NOTICE. Generally
	 * corresponds to E_STRICT, E_NOTICE, or E_USER_NOTICE errors
	 *
	 * @param string $line Information to log
	 * @param string $label
	 * @return void
	 */
	public static function logNotice( $line, $label = 'log' ) {
		self::log( $line, self::NOTICE, $label );
	}

	/**
	 * Writes a $line to the log with a severity level of WARN. Generally
	 * corresponds to E_WARNING, E_USER_WARNING, E_CORE_WARNING, or
	 * E_COMPILE_WARNING
	 *
	 * @param string $line Information to log
	 * @param string $label
	 * @return void
	 */
	public static function logWarn( $line, $label = 'log' ) {
		self::log( $line, self::WARN, $label );
	}

	/**
	 * Writes a $line to the log with a severity level of ERR. Most likely used
	 * with E_RECOVERABLE_ERROR
	 *
	 * @param string $line Information to log
	 * @param string $label
	 * @return void
	 */
	public static function logError( $line, $label = 'log' ) {
		self::log( $line, self::ERR, $label );
	}

	/**
	 * Writes a $line to the log with a severity level of FATAL. Generally
	 * corresponds to E_ERROR, E_USER_ERROR, E_CORE_ERROR, or E_COMPILE_ERROR
	 *
	 * @param string $line Information to log
	 * @param string $label
	 * @return void
	 * @deprecated Use logCrit
	 */
	public static function logFatal( $line, $label = 'log' ) {
		self::log( $line, self::FATAL, $label );
	}

	/**
	 * Writes a $line to the log with a severity level of ALERT.
	 *
	 * @param string $line Information to log
	 * @param string $label
	 * @return void
	 */
	public static function logAlert( $line, $label = 'log' ) {
		self::log( $line, self::ALERT, $label );
	}

	/**
	 * Writes a $line to the log with a severity level of CRIT.
	 *
	 * @param string $line Information to log
	 * @param string $label
	 * @return void
	 */
	public static function logCrit( $line, $label = 'log' ) {
		self::log( $line, self::CRIT, $label );
	}

	/**
	 * Writes a $line to the log with a severity level of EMERG.
	 *
	 * @param string $line Information to log
	 * @param string $label
	 * @return void
	 */
	public static function logEmerg( $line, $label = 'log' ) {
		self::log( $line, self::EMERG, $label );
	}

	/**
	 * Writes a line to the log without prepending a status or timestamp
	 *
	 * @param string $line Line to write to the log
	 * @param string $label
	 * @throws \UnexpectedValueException
	 * @throws \IOException
	 * @return void
	 */
	public static function writeLine( $line, $label = 'log' ) {
		if ( self::$isSetup === false ) {
			self::setup();
		}

		if( !array_key_exists( $label, self::$fileHandles ) || !array_key_exists( $label, self::$severityThresholds ) ){
			throw new \UnexpectedValueException ( "Log file for label {$label} has not bee setup" );
		}
		if ( self::$severityThresholds[ $label ] != self::OFF ) {
			if ( fwrite( self::$fileHandles[$label], $line ) === false ) {
				throw new \IOException( "Failed to write log to the log file for label {$label}" );
			}
		}
	}

	private static function getTimeLine( $level ) {
		$time = date( 'Y-m-d G:i:s' );

		switch ( $level ) {
			case self::EMERG:
				return "$time - EMERG -->";
			case self::ALERT:
				return "$time - ALERT -->";
			case self::CRIT:
				return "$time - CRIT -->";
			case self::FATAL: # FATAL is an alias of CRIT
				return "$time - FATAL -->";
			case self::NOTICE:
				return "$time - NOTICE -->";
			case self::INFO:
				return "$time - INFO -->";
			case self::WARN:
				return "$time - WARN -->";
			case self::DEBUG:
				return "$time - DEBUG -->";
			case self::ERR:
				return "$time - ERROR -->";
			default:
				return "$time - LOG -->";
		}
	}
}

/**
 * Class LoggerDestructor
 * This is used to destruct the above class
 */
class LoggerDestructor {
	public function __destruct() {
		Logger::_destruct();
	}
}