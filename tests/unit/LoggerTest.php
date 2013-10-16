<?php

namespace Addframe\Test\Unit;

use Addframe\Logger;

/**
 * Class LoggerTest
 * @covers Addframe\Logger
 *
 * This set of tests can not be run along side another set oo logger tests
 * This is due to the fact that the Logger class holds the files open
 */

class LoggerTest extends DefaultTestCase{

	/**
	 * @see PHPUnit_Framework_TestCase::setUp()
	 */
	protected function setUp() {
		parent::setUp();

		//Delete any files that already exist
		if( file_exists( self::getExpectedPath( self::$logLabel ) ) ){
			unlink( self::getExpectedPath( self::$logLabel ) );
		}

	}

	/**
	 * @see PHPUnit_Framework_TestCase::tearDown()
	 */
	protected function tearDown() {
		parent::tearDown();

		//Make the logger resets
		Logger::_reset();

		//Delete the files
		if( file_exists( self::getExpectedPath( self::$logLabel ) ) ){
			unlink( self::getExpectedPath( self::$logLabel ) );
		}

	}

	/**
	 * @see PHPUnit_Framework_TestCase::tearDownAfterClass()
	 */
	public static function tearDownAfterClass(){
		parent::tearDownAfterClass();

		//Delete the logger folder we created
		if( file_exists( __DIR__.'/../../log/'.self::$logLabel ) ){
			rmdir( __DIR__.'/../../log/'.self::$logLabel );
		}

	}

	/**
	 * logLabel to be used throughout this set of tests
	 */
	protected static $logLabel = 'testLog';

	/**
	 * Method to get the expected location of the log
	 * this is constructed using the label and current date
	 * @return string fullpath location
	 */
	static function getExpectedPath( ){
		return __DIR__.'/../../log/'.self::$logLabel.'/'.date( 'Y-m-d' ) . '.txt';
	}

	/**
	 * Get the contents of the log file we are using
	 * @return string file contents
	 */
	static function getFile( ){
		return file_get_contents( self::getExpectedPath() );
	}

	/**
	 * Test that each level of logging logs correctly when the log is set to DEBUG
	 */
	public function testAllLogMethodsInDebug(){
		$this->assertFileNotExists( self::getExpectedPath( self::$logLabel ) );

		Logger::setupLog( self::$logLabel, Logger::DEBUG );
		$this->assertFileExists( self::getExpectedPath( self::$logLabel ) );
		$this->assertEmpty( file_get_contents( self::getExpectedPath( self::$logLabel ) ) );

		$testString = "testAllLogMethodsInDebug";

		$this->assertNotContains( " - EMERG --> {$testString}", self::getFile() );
		Logger::logEmerg( $testString,self::$logLabel );
		$this->assertContains( " - EMERG --> {$testString}", self::getFile() );

		$this->assertNotContains( " - ALERT --> {$testString}", self::getFile() );
		Logger::logAlert( $testString,self::$logLabel );
		$this->assertContains( " - ALERT --> {$testString}", self::getFile() );

		$this->assertNotContains( " - CRIT --> {$testString}1", self::getFile() );
		Logger::logCrit( $testString.'1',self::$logLabel );
		$this->assertContains( " - CRIT --> {$testString}1", self::getFile() );

		$this->assertNotContains( " - CRIT --> {$testString}2", self::getFile() );
		Logger::logFatal( $testString.'2',self::$logLabel );
		$this->assertContains( " - CRIT --> {$testString}2", self::getFile() );

		$this->assertNotContains( " - ERROR --> {$testString}", self::getFile() );
		Logger::logError( $testString,self::$logLabel );
		$this->assertContains( " - ERROR --> {$testString}", self::getFile() );

		$this->assertNotContains( " - WARN --> {$testString}", self::getFile() );
		Logger::logWarn( $testString,self::$logLabel );
		$this->assertContains( " - WARN --> {$testString}", self::getFile() );

		$this->assertNotContains( " - NOTICE --> {$testString}", self::getFile() );
		Logger::logNotice( $testString,self::$logLabel );
		$this->assertContains( " - NOTICE --> {$testString}", self::getFile() );

		$this->assertNotContains( " - INFO --> {$testString}", self::getFile() );
		Logger::logInfo( $testString,self::$logLabel );
		$this->assertContains( " - INFO --> {$testString}", self::getFile() );

		$this->assertNotContains( " - DEBUG --> {$testString}", self::getFile() );
		Logger::logDebug( $testString,self::$logLabel );
		$this->assertContains( " - DEBUG --> {$testString}", self::getFile() );
	}

	/**
	 * Test that when logging is turned off we do not log anything
	 */
	public function testAllLogMethodsInOff(){
		if( file_exists( self::getExpectedPath( self::$logLabel ) ) ){
			unlink( self::getExpectedPath( self::$logLabel ) );
		}
		$this->assertFileNotExists( self::getExpectedPath( self::$logLabel ) );

		Logger::setDefaultSeverityThreshold( Logger::OFF );

		Logger::setupLog( self::$logLabel );
		$this->assertFileExists( self::getExpectedPath( self::$logLabel ) );

		$testString = "testAllLogMethodsInDebug";

		Logger::logEmerg( $testString,self::$logLabel );
		Logger::logAlert( $testString,self::$logLabel );
		Logger::logCrit( $testString,self::$logLabel );
		Logger::logFatal( $testString,self::$logLabel );
		Logger::logError( $testString,self::$logLabel );
		Logger::logWarn( $testString,self::$logLabel );
		Logger::logNotice( $testString,self::$logLabel );
		Logger::logInfo( $testString,self::$logLabel );
		Logger::logDebug( $testString,self::$logLabel );
		$this->assertEmpty( file_get_contents( self::getExpectedPath( self::$logLabel ) ) );
	}

	/**
	 * Make sure we don't get file errors if we try to setup to identical logs
	 * (these errors would eb caused as the files are kept open bu the logger)
	 */
	public function testCanOnlySetupOnce(){
		Logger::setupLog( self::$logLabel, Logger::OFF );
		Logger::setupLog( self::$logLabel, Logger::OFF );
		$this->assertTrue( true );
	}

}