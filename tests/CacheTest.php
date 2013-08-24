<?php

use Addframe\Mediawiki\ApiRequest;
use Addframe\Cache;

class CacheTest extends PHPUnit_Framework_TestCase{

	function testCacheTrip(){
		// setup 2 requests
		$request1 = $this->getRandomRequest();
		$request2 = $this->getRandomRequest();

		// assert neither result is currently in the cache
		$this->assertFalse( Cache::has( $request1->getHash() ) );
		$this->assertFalse( Cache::has( $request2->getHash() ) );

		// assert the first cache is added correctly
		Cache::add( $request1 );
		$this->assertTrue( Cache::has( $request1->getHash() ) );
		$this->assertEquals( $request1->getResult(), Cache::get( $request1->getHash() ) );

		// assert the second cache is added correctly (and the first is still there)
		Cache::add( $request2 );
		$this->assertTrue( Cache::has( $request1->getHash() ) );
		$this->assertEquals( $request1->getResult(), Cache::get( $request1->getHash() ) );
		$this->assertTrue( Cache::has( $request2->getHash() ) );
		$this->assertEquals( $request2->getResult(), Cache::get( $request2->getHash() ) );

		// remove the first result and make sure the second is still there
		Cache::remove( $request1->getHash() );
		$this->assertFalse( Cache::has( $request1->getHash() ) );//todo fix removing of cached results
		$this->assertTrue( Cache::has( $request2->getHash() ) );
		$this->assertEquals( $request2->getResult(), Cache::get( $request2->getHash() ) );

		// clear the cache and assert neither result is there
		Cache::clear();
		$this->assertFalse( Cache::has( $request1->getHash() ) );//todo fix removing of cached results
		$this->assertFalse( Cache::has( $request2->getHash() ) );//todo fix removing of cached results

	}

	function getRandomRequest(){
		$request = new ApiRequest( array( rand( 0, 99999999 ) ) );
		$request->setResult( array( 'Note' => 'This cached result was generated in a test' ) );
		return $request;
	}

}