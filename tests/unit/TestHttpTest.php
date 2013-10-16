<?php

namespace Addframe\Test\Unit;

use Addframe\TestHttp;

class TestHttpTest extends DefaultTestCase{

	function testTestHttpWithArray(){
		$testData = '{}';
		$testHttp = new TestHttp( array( $testData.'1', $testData.'2' ) );
		$this->assertEquals( $testData.'1', $testHttp->get( 'testurl' ) );
		$this->assertEquals( $testData.'2', $testHttp->post( 'testurl', array() ) );
	}

	function testTestHttpWithString(){
		$testData = '{}';
		$testHttp = new TestHttp( $testData );
		$this->assertEquals( $testData, $testHttp->get( 'testurl' ) );
		$this->assertEquals( $testData, $testHttp->post( 'testurl', array() ) );
	}

}