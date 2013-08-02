<?php

namespace Addframe\Tests;

use Addframe\Coordinate;

/**
 * @since 0.0.2
 *
 * @author Addshore
 */

class CoordinateTest extends \PHPUnit_Framework_TestCase {

	function provideCoordinateParams(){
		return array(
			array( '29_35_17_N_82_5_2_W_region:US_type:city',
				array(
					'latitude' => '29.588055555556',
					'longitude' => '-82.083888888889',
					'precision' => strval( 1/60/60 )
				)
			),
			array( '29_35__S_82_5__E',
				array(
					'latitude' => '-29.583333333333',
					'longitude' => '82.083333333333',
					'precision' => strval( 1/60 )
				)
			),
			array( '22___N_5___E',
				array(
					'latitude' => '22',
					'longitude' => '5',
					'precision' => '1'
				)
			),
		);
	}

	/**
	 * @dataProvider provideCoordinateParams
	 */
	function testCanConstructCoordinate( $params ){
		new Coordinate( $params );
		$this->assertTrue( true , "Failed to construct coordinate");
	}

	/**
	 * @dataProvider provideCoordinateParams
	 */
	function testCanGetWikidataArray( $params, $expectedArray ){
		$coor = new Coordinate( $params );
		$array = $coor->getWikidataArray();
		$this->assertEquals( $expectedArray, $array, "Failed to assert Coordinate array was correct" );
	}

}