<?php

namespace Addframe\Tests;
use Addframe\AutoLoader;

/**
 * @since 0.0.4
 *
 * @author Addshore
 */

class AutoLoaderTest extends \PHPUnit_Framework_TestCase {

	public function testCanLoadRegisteredClasses(){
		foreach( AutoLoader::$classNames as $className => $location ){
			$this->assertTrue( AutoLoader::loadClass( $className ) );
		}
	}

	//This must be at the bottom so we do not try to load the non existing dummy class
	public function testCanRegisterClass(){
		AutoLoader::registerClass('className', 'FilePath');
		$this->assertArrayHasKey('className', AutoLoader::$classNames );
		$this->assertEquals( 'FilePath', AutoLoader::$classNames['className'] );
	}

}