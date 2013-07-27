<?php

namespace Addframe\Tests;

use Addframe\Registry;

class ReqistryTest extends \PHPUnit_Framework_TestCase {

	function testCanConstructRegistry(){
		new Registry();
		$this->assertTrue( true );
	}

}