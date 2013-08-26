<?php

/**
 * Class DefaultTestCase
 */
class DefaultTestCase extends PHPUnit_Framework_TestCase {

	public static function tearDownAfterClass() {
		\Addframe\Cache::clear();
	}

}