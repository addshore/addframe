<?php

namespace Addframe\Test;

/**
 * Class DefaultTestCase
 */
class DefaultTestCase extends \PHPUnit_Framework_TestCase {

	/**
	 * @see PHPUnit_Framework_TestCase::tearDownAfterClass()
	 */
	public static function tearDownAfterClass() {
		/*
		 * Clear the cache between each test class (just in case there is stuff in there)
		 * Generally if stuff needs to remain cached it only needs to remain within the same test class
		 */
		\Addframe\Cache::clear();
	}

}