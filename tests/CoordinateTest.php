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
			//0 regular with data
			array( '29_35_17_N_82_5_2_W_region:US_type:city',
				array(
					'latitude' => '29.588055555556',
					'longitude' => '-82.083888888889',
					'precision' => strval( 1/60/60 )
				)
			),
			//1 regular 1arc min
			array( '29_35__S_82_5__E',
				array(
					'latitude' => '-29.583333333333',
					'longitude' => '82.083333333333',
					'precision' => strval( 1/60 )
				)
			),
			//2 regular degree only
			array( '22___N_5___E',
				array(
					'latitude' => '22',
					'longitude' => '5',
					'precision' => '1'
				)
			),
			//3 regular degree with 0
			array( '29_0__N_5_0__E',
				array(
					'latitude' => '29',
					'longitude' => '5',
					'precision' => strval( 1/60 )
				)
			),
			//4 regular degree with 0s
			array( '22_0_0_N_2_0_0_E',
				array(
					'latitude' => '22',
					'longitude' => '2',
					'precision' => strval( 1/60/60 )
				)
			),
			//5 regular degree with 0s in arcmin
			array( '22.1_0_6_N_2_0_1_E',
				array(
					'latitude' => '22.101666666667',
					'longitude' => '2.0002777777778',
					'precision' => strval( 1/60/60 )
				)
			),
//			//6 Decimal degree only
//			array( '29.583333333333___N_82.083333333333___E',
//				array(
//					'latitude' => '29.583333333333',
//					'longitude' => '82.083333333333',
//					'precision' => strval( 1/60 )
//				)
//			),
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