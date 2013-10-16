<?php

namespace Addframe;

use IOException;
use UnexpectedValueException;

/**
 * A light, permissions-checking logging class.
 *
 * Originally written for use with wpSearch
 * @author  Kenny Katzgrau <katzgrau@gmail.com>
 * @since   July 26, 2008 — Last update July 1, 2012
 * @link    http://codefury.net
 * @version 0.2.0
 *
 * @author Addshore ( Modified for use with Addframe )
 *
 */

class Logger {

	/**
	 * Error severity, from low to high. From BSD syslog RFC, secion 4.1.1
	 * @link http://www.faqs.org/rfcs/rfc3164.html
	 */
	const ALL = 0;
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
	 * Flag to show if the class has been setup() yet
	 * @var bool
	 */
	private static $isSetup = false;
	/**
	 * Path to the logging directory
	 * @var string
	 */
	private static $logDirectory;
	/**
	 * Current minimum logging threshold identified by label
	 * @var integer[]
	 */
	private static $severityThresholds = array();
	/**
	 * This holds the file paths for logs identified by label
	 * @var resource[]
	 */
	private static $filePaths = array();
	/**
	 * This is the default severityThreshold for newly created logs
	 * @var int
	 */
	private static $defaultSeverityThreshold = Logger::INFO;

	/**
	 * Get the class ready to use, Called automatically from within this class
	 */
	private static function setup() {
		if ( self::$isSetup === false ) {

			//set up our log directory
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
	 * Resets Class defaults, resets any changed vars
	 */
	public static function _reset() {
		self::$filePaths = array();
		self::$severityThresholds = array();
		self::$isSetup = false;
	}

	/**
	 * Sets the default severityThreshold for newly created logs
	 * @param $severity int Severity to change to default
	 */
	public static function setDefaultSeverityThreshold( $severity ){
		self::$defaultSeverityThreshold = $severity;
	}

	/**
	 * Setup a new log for the given label
	 * @param string $label Label of the log
	 * @param null|int $severity
	 * @param int $severity
	 * @throws IOException
	 */
	public static function setupLog( $label = 'log', $severity = null ) {
		if ( ! array_key_exists( $label, self::$filePaths ) ) {

			//make sure the class is ready
			if ( self::$isSetup === false ) {
				self::setup();
			}

			//get the default severity if none is set
			if( is_null( $severity ) ){
				$severity = self::$defaultSeverityThreshold;
			}

			//get the location of our log
			if ( $label === 'log' ) {
				$logFilePath = self::$logDirectory . '/' . date( 'Y-m-d' ) . '.txt';
			} else {
				$logFileDirectory = self::$logDirectory . '/' . $label;
				if ( ! file_exists( $logFileDirectory ) ) {
					if( mkdir( $logFileDirectory, 0777, true ) === false ){
						throw new IOException( 'Failed to make directory ' . $logFileDirectory );
					}
				}
				$logFilePath = $logFileDirectory . '/' . date( 'Y-m-d' ) . '.txt';
			}

			//create the log file if it doesn't exist (even if we end up not using it)
			if ( !file_exists( $logFilePath ) ){
				if( file_put_contents( $logFilePath, '', FILE_APPEND ) === false ){
					throw new IOException( "Can not create log path {$logFilePath}" );
				}
			}

			if ( !( file_exists( $logFilePath ) && ! is_writable( $logFilePath ) ) ) {
				//register this log
				self::$severityThresholds[$label] = $severity;
				self::$filePaths[$label] = $logFilePath;
			} else {
				throw new IOException( "Can not write to log path {$logFilePath}" );
			}
		}
	}

	/**
	 * Writes a $line to the log with the given severity
	 * Also carries out the check to see if we should be logging the given message
	 *
	 * @param string $line Text to add to the log
	 * @param integer $severity Severity level of log message (use constants)
	 * @param string $label Label of the log to log to
	 * @throws UnexpectedValueException
	 */
	public static function log( $line, $severity = Logger::INFO, $label = 'log' ) {
		//make sure we are setup
		if ( self::$isSetup === false ) {
			self::setup();
		}

		//make sure the log is ready
		if( !array_key_exists( $label, self::$filePaths ) || !array_key_exists( $label, self::$severityThresholds ) ){
			throw new UnexpectedValueException ( "Log file for label {$label} has not bee setup" );
		}

		//make sure we should actually be logging
		if ( self::$severityThresholds[ $label ] >= $severity ) {
			$status = self::getTimeLine( $severity );
			self::writeLine( "$status $line" . PHP_EOL, $label );
		}
	}

	/**
	 * Writes a $line to the log with a severity level of NOTICE.
	 *
	 * @param string $line Information to log
	 * @param string $label Label of the log to log to
	 */
	public static function logDebug( $line, $label = 'log' ) {
		self::log( $line, self::DEBUG, $label );
	}

	/**
	 * Writes a $line to the log with a severity level of INFO.
	 *
	 * @param string $line Information to log
	 * @param string $label Label of the log to log to
	 */
	public static function logInfo( $line, $label = 'log' ) {
		self::log( $line, self::INFO, $label );
	}

	/**
	 * Writes a $line to the log with a severity level of NOTICE. Generally
	 * corresponds to E_STRICT, E_NOTICE, or E_USER_NOTICE errors
	 *
	 * @param string $line Information to log
	 * @param string $label Label of the log to log to
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
	 */
	public static function logWarn( $line, $label = 'log' ) {
		self::log( $line, self::WARN, $label );
	}

	/**
	 * Writes a $line to the log with a severity level of ERR. Most likely used
	 * with E_RECOVERABLE_ERROR
	 *
	 * @param string $line Information to log
	 * @param string $label Label of the log to log to
	 */
	public static function logError( $line, $label = 'log' ) {
		self::log( $line, self::ERR, $label );
	}

	/**
	 * Writes a $line to the log with a severity level of FATAL. Generally
	 * corresponds to E_ERROR, E_USER_ERROR, E_CORE_ERROR, or E_COMPILE_ERROR
	 *
	 * @param string $line Information to log
	 * @param string $label Label of the log to log to
	 * @deprecated Use logCrit
	 */
	public static function logFatal( $line, $label = 'log' ) {
		self::log( $line, self::FATAL, $label );
	}

	/**
	 * Writes a $line to the log with a severity level of ALERT.
	 *
	 * @param string $line Information to log
	 * @param string $label Label of the log to log to
	 */
	public static function logAlert( $line, $label = 'log' ) {
		self::log( $line, self::ALERT, $label );
	}

	/**
	 * Writes a $line to the log with a severity level of CRIT.
	 *
	 * @param string $line Information to log
	 * @param string $label Label of the log to log to
	 */
	public static function logCrit( $line, $label = 'log' ) {
		self::log( $line, self::CRIT, $label );
	}

	/**
	 * Writes a $line to the log with a severity level of EMERG.
	 *
	 * @param string $line Information to log
	 * @param string $label Label of the log to log to
	 */
	public static function logEmerg( $line, $label = 'log' ) {
		self::log( $line, self::EMERG, $label );
	}

	/**
	 * Writes a line to the log without prepending a status or timestamp
	 *
	 * @param string $line Line to write to the log
	 * @param string $label Label of the log to log to
	 * @throws UnexpectedValueException
	 * @throws IOException
	 */
	private static function writeLine( $line, $label = 'log' ) {
		//make sure logging is not turned off
		if ( self::$severityThresholds[ $label ] !== self::OFF ) {
			if ( file_put_contents(self::$filePaths[ $label ], $line, FILE_APPEND) === false ) {
				throw new IOException( "Failed to write log to the log file for label {$label}" );
			}
		}
	}

	/**
	 * Automatically get the start of the log line
	 * @param $level int Severity level of log message (use constants)
	 * @return string
	 */
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