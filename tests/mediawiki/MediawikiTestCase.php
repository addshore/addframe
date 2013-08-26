<?php

/**
 * Class MediawikiTestCase adding some extra functions
 */
class MediawikiTestCase extends DefaultTestCase {

	/**
	 * @param $path string of data to get
	 * @return string data from path
	 */
	protected function getData( $path ){
		return file_get_contents( __DIR__.'/data/'.$path );
	}

}