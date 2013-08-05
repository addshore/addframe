<?php

namespace Addframe\Tests;
use Addframe\Config;

/**
 * @since 0.0.4
 *
 * @author Addshore
 */

class ConfigTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @dataProvider provideConfigOptions
	 */
	function testCanSetGetRoundtrip( $where, $what ){
		Config::set($where, $what);
		$this->assertEquals( $what,  Config::get($where) );
	}

	function provideConfigOptions(){
		return array(
			array( 'settingname', 'setting value' ),
			array( 'setting.name', array('value1', 'value2') ),
			array( 'integer!', 12 ),
		);
	}

	function testLoadConfigException(){
		$this->setExpectedException('UnexpectedValueException');
		Config::loadConfigs('ThisPathDoesClearlyNotExist');
	}

}