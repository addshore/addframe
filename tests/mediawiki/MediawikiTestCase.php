<?php

/**
 * Class MediawikiTestCase adding some extra functions
 */
class MediawikiTestCase extends DefaultTestCase {

	/**
	 * @param $path string of data to get
	 * @throws UnexpectedValueException
	 * @return string data from path
	 */
	protected function getData( $path ){
		$path =  __DIR__.'/data/'.$path;
		$data = file_get_contents( $path );

		//If there is no data throw an exception, we should define data!
		if( is_null( $data ) || $data === false || !is_string( $data ) || empty( $data ) ){
			throw new UnexpectedValueException( "No data got (you should define it) from {$path}, '{$data}'" );
		}

		return $data;
	}

}