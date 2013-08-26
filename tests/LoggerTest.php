<?php

use Addframe\Logger;

/**
 * Class LoggerTest
 * @covers Addframe\Logger
 *
 * This set of tests can not be run along side another set oo logger tests
 * This is due to the fact that the Logger class holds the files open
 */

class LoggerTest extends DefaultTestCase{

	protected static $logLabel = 'testLog';

	static function getExpectedPath( ){
		return __DIR__.'/../log/'.self::$logLabel.'/'.date( 'Y-m-d' ) . '.txt';
	}
	static function getFile( ){
		return file_get_contents( self::getExpectedPath() );
	}

	protected function setUp() {
		parent::setUp();

		if( file_exists( self::getExpectedPath( self::$logLabel ) ) ){
			unlink( self::getExpectedPath( self::$logLabel ) );
		}

	}

	protected function tearDown() {
		parent::tearDown();

		Logger::_destruct();
	}

	//Tests are below this line ---------------

	function testAllLogMethodsInDebug(){
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

	function testAllLogMethodsInOff(){
		if( file_exists( self::getExpectedPath( self::$logLabel ) ) ){
			unlink( self::getExpectedPath( self::$logLabel ) );
		}
		$this->assertFileNotExists( self::getExpectedPath( self::$logLabel ) );

		Logger::setupLog( self::$logLabel, Logger::OFF );
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

	function testCanOnlySetupOnce(){
		Logger::setupLog( self::$logLabel, Logger::OFF );
		Logger::setupLog( self::$logLabel, Logger::OFF );
		$this->assertTrue( true );
	}

}