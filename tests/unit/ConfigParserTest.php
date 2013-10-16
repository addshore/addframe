<?php

namespace Addframe\Test;

use Addframe\ConfigParser;

/**
 * Class ConfigParserTest
 * @covers \Addframe\ConfigParser
 */
class ConfigParserTest extends DefaultTestCase {

	function testCanParseDefaultConfig(){
		$configParser = new ConfigParser();
		$this->assertEquals( 'AddFrame', $configParser->get( 'FrameworkName' ) );
	}

	function testCanParseEnvironment(){
		$configParser = new ConfigParser( 'addframephpunittest1' );
		$this->assertEquals( 'AddFrame', $configParser->get( 'FrameworkName' ) );
		$this->assertEquals( 'foo', $configParser->get( 'testvar1' ) );

	}

	function testCanParseEnvironmentExtended(){
		$configParser = new ConfigParser( 'addframephpunittest2' );
		$this->assertEquals( 'AddFrame', $configParser->get( 'FrameworkName' ) );
		$this->assertEquals( 'foo', $configParser->get( 'testvar1' ) );
		$this->assertEquals( 'bar', $configParser->get( 'testvar2' ) );

	}

	function testExceptionOnBadEnvironment(){
		$this->setExpectedException( 'Exception' );
		new ConfigParser( 'qwertyuiopasdfghje' );
	}

}