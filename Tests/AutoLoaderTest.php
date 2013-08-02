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

}