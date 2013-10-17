<?php

namespace Addframe\Test\Unit;

use Addframe\Http;
use Addframe\TestHttp;

/**
 * Class HttpTest
 * @covers Addframe\Http
 * @covers Addframe\TestHttp
 */

class HttpTest extends \PHPUnit_Framework_TestCase {

	public function testCanGetDefaultInstance(){
		$http = Http::getDefaultInstance();
		$http2 = Http::getDefaultInstance();

		$this->assertInstanceOf( 'Addframe\Http', $http );
		$this->assertInstanceOf( 'Addframe\Http', $http2 );

		$this->assertEquals( $http->getUid(), $http2->getUid() );
	}

	public function testCanConstruct(){
		$http = new Http();
		$http2 = new Http();

		$this->assertInstanceOf( 'Addframe\Http', $http );
		$this->assertInstanceOf( 'Addframe\Http', $http2 );

		$this->assertNotEquals( $http->getUid(), $http2->getUid() );
	}

	/**
	 * @dataProvider provideDataToEncode
	 */
	public function testEncodeData( $data, $expected ){
		$http = new TestHttp();
		$encodedData = $http->encodeData( $data );
		$this->assertEquals( $expected, $encodedData );
	}

	public function provideDataToEncode(){
		return array(
			//to encode, //encoded
			array( array( 'key' => 'value' ), 'key=value' ),
			array( array( 'key_12' => 'value spaced' ), 'key_12=value+spaced' ),
			array( array( 'chars' => '&&$$""' ), 'chars=%26%26%24%24%22%22' ),
		);
	}

	public function testGettingInvalidUrlReturnsFalse(){
		$this->setExpectedException( '\Addframe\HttpException' );
		$http = new Http();
		$http->get( '2387ry389t32u89tu*(&$HE98rh98' );
	}

	public function testPostingInvalidUrlReturnsFalse(){
		$this->setExpectedException( '\Addframe\HttpException' );
		$http = new Http();
		$http->post( '2387ry389t32u89tu*(&$HE98rh98', array() );
	}

}