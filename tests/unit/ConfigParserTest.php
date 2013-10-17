<?php

namespace Addframe\Test\Unit;

use Addframe\ConfigParser;

/**
 * Class ConfigParserTest
 * @covers \Addframe\ConfigParser
 */
class ConfigParserTest extends \PHPUnit_Framework_TestCase {

	public function testCanParseDefaultConfig(){
		$configParser = new ConfigParser();
		$this->assertEquals( 'AddFrame', $configParser->get( 'FrameworkName' ) );
	}

	public function testCanParseEnvironment(){
		$configParser = new ConfigParser( 'addframephpunittest1' );
		$this->assertEquals( 'AddFrame', $configParser->get( 'FrameworkName' ) );
		$this->assertEquals( 'foo', $configParser->get( 'testvar1' ) );

	}

	public function testCanParseEnvironmentExtended(){
		$configParser = new ConfigParser( 'addframephpunittest2' );
		$this->assertEquals( 'AddFrame', $configParser->get( 'FrameworkName' ) );
		$this->assertEquals( 'foo', $configParser->get( 'testvar1' ) );
		$this->assertEquals( 'bar', $configParser->get( 'testvar2' ) );

	}

	public function testExceptionOnBadEnvironment(){
		$this->setExpectedException( 'Exception' );
		new ConfigParser( 'qwertyuiopasdfghje' );
	}

}