<?php

namespace Addframe\Tests;

use Addframe\Registry;

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
	function testCanSetValue( $value ){
		$registry = new Registry();
		$registry->$value[0] = $value[1];
		$this->assertEquals( $value[1], $registry->objects[$value[0]], 'Failed to set value in the registry' );
	}

	function provideValidValues(){
		return array(
			array( array('anEmptyString' , '') ),
			array( array('aString','aStringValue' ) ),
			array( array('anArray', array('item1', 'item2') ) ),
			array( array('anobject', new \Exception( 'Test object' ) ) ),
		);
	}

	/**
	 * @dataProvider provideValidValues
	 */
	function testCanSetGetRoundtrip( $value ) {
		$registry = new Registry();
		$registry->$value[0] = $value[1];
		$this->assertEquals( $value[1], $registry->$value[0], 'Failed to set and then get value in the registry' );
	}
}