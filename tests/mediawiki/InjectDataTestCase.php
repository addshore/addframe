<?php


/**
 * Class InjectDataTestCase adding some extra functions
 */
class InjectDataTestCase extends PHPUnit_Framework_TestCase {

	/**
	 * @param $path string of data to get
	 * @return string data from path
	 */
	protected function getData( $path ){
		return file_get_contents( __DIR__.'/data/'.$path );
	}

}