<?php

namespace Addframe\Test\Unit;

use Addframe\Mediawiki\Api\Request;
use Addframe\Mediawiki\TestApi;

/**
 * Class TestApiTest
 * @covers Addframe\Mediawiki\TestApi
 */
class TestApiTest extends DefaultTestCase{

	function testTestApiWithArray(){
		$expected1 = array( 'testTestApi array1' );
		$expected2 = array( 'testTestApi array2' );
		$testApi = new TestApi( array( json_encode( $expected1 ), json_encode( $expected2 ) ) );
		$request = new Request();

		$result = $testApi->doRequest( $request, false );
		$this->assertEquals( $expected1, $result );
		$this->assertEquals( $expected1, $request->getResult() );

		$result = $testApi->doRequest( $request, false );
		$this->assertEquals( $expected2, $result );
		$this->assertEquals( $expected2, $request->getResult() );
	}

	function testTestApiWithString(){
		$expected = array( 'testTestApi string' );
		$testApi = new TestApi( json_encode( $expected ) );

		$request = new Request();
		$result = $testApi->doRequest( $request, false );
		$this->assertEquals( $expected, $result );
		$this->assertEquals( $expected, $request->getResult() );

		$result = $testApi->doRequest( $request, false );
		$this->assertEquals( $expected, $result );
		$this->assertEquals( $expected, $request->getResult() );
	}

	function testTestApiHoldsResults(){
		$testApi = new TestApi( '[]' );

		$request1 = new Request( array( 'label' => 'unique1' ) );
		$request2 = new Request( array( 'label' => 'unique2' ) );

		$this->assertFalse( in_array( $request1, $testApi->completeRequests ) );
		$this->assertEquals( 0, count( $testApi->completeRequests ) );

		$testApi->doRequest( $request1, false );
		$this->assertTrue( in_array( $request1, $testApi->completeRequests ) );
		$this->assertEquals( 1, count( $testApi->completeRequests ) );

		$testApi->doRequest( $request2, false );
		$this->assertTrue( in_array( $request2, $testApi->completeRequests ) );
		$this->assertEquals( 2, count( $testApi->completeRequests ) );

		$this->assertEquals( $request1, $testApi->completeRequests[0] );
		$this->assertEquals( $request2, $testApi->completeRequests[1] );
	}

}