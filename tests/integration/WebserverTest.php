<?php

namespace Addframe\Test\Integration;

/**
 * This test class simply checks to make sure the webserver is where we expect it to be
 */
class WebserverTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @dataProvider provideUrls
	 */
	public function testCanAccessUrls( $url ) {
		$data = $this->get_data( $url );
		$this->assertNotEmpty( $data );
	}

	public function provideUrls(){
		$builder = array();

		$builder[] = 'http://localhost';
		$builder[] = 'http://localhost/wiki/index.php';
		$builder[] = 'http://localhost/wiki/api.php';
		$builder[] = SITEURL;

		$urls = array();
		foreach( $builder as $url ){
			$urls[] = array( $url );
		}
		return $urls;
	}

	protected function get_data( $url ) {
		$ch = curl_init();
		curl_setopt( $ch, CURLOPT_URL, $url );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, 5 ); // dont time out too quickly
		curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true ); //make sure we follow redirects
		$data = curl_exec( $ch );
		curl_close( $ch );
		return $data;
	}

}