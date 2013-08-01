<?php

namespace Addframe\Tests;

use Addframe\Registry;

/**
 *
 * @since 0.0.1
 *
 * @author Addshore
 */

class ReqistryTest extends \PHPUnit_Framework_TestCase {

	function testCanConstructRegistry(){
		new Registry();
		$this->assertTrue( true, 'Unable to construct a Registry object' );
	}

	function testExceptionIfGetUnsetValue(){
		$registry = new Registry();
		$index = 'nothingHere';
		$this->setExpectedException('Exception', "Undefined index '$index' in registry");
		$registry->$index;
	}

	/**
	 * @dataProvider provideValidValues
	 */
	function testCanSetValue( $key, $value ){
		$registry = new Registry();
		$registry->$key = $value;
		$this->assertEquals( $value, $registry->objects[$key], 'Failed to set value in the registry' );
	}

	function provideValidValues(){
		return array(
			array( 'anEmptyString' , '' ),
			array( 'aString','aStringValue' ),
			array( 'anArray', array('item1', 'item2') ),
			array( 'anobject', new \Exception( 'Test object' ) ),
		);
	}

	/**
	 * @dataProvider provideValidValues
	 */
	function testCanSetGetRoundtrip( $key, $value ) {
		$registry = new Registry();
		$registry->$key = $value;
		$this->assertEquals( $value, $registry->$key, 'Failed to set and then get value in the registry' );
	}
}