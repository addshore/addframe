<?php
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

}