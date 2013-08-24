<?php

use Addframe\Mediawiki\ApiRequest;
use Addframe\Mediawiki\ResultCache;

class ResultCacheTest extends PHPUnit_Framework_TestCase{

	function testCacheTrip(){
		// setup 2 requests
		$request1 = $this->getRandomRequest();
		$request2 = $this->getRandomRequest();

		// assert neither result is currently in the cache
		$this->assertFalse( ResultCache::hasResultWithHash( $request1->getHash() ) );
		$this->assertFalse( ResultCache::hasResultWithHash( $request2->getHash() ) );

		// assert the first cache is added correctly
		ResultCache::addResult( $request1 );
		$this->assertTrue( ResultCache::hasResultWithHash( $request1->getHash() ) );
		$this->assertEquals( $request1->getResult(), ResultCache::getResultWithHash( $request1->getHash() ) );

		// assert the second cache is added correctly (and the first is still there)
		ResultCache::addResult( $request2 );
		$this->assertTrue( ResultCache::hasResultWithHash( $request1->getHash() ) );
		$this->assertEquals( $request1->getResult(), ResultCache::getResultWithHash( $request1->getHash() ) );
		$this->assertTrue( ResultCache::hasResultWithHash( $request2->getHash() ) );
		$this->assertEquals( $request2->getResult(), ResultCache::getResultWithHash( $request2->getHash() ) );

		// remove the first result and make sure the second is still there
		ResultCache::removeResultWithHash( $request1->getHash() );
		$this->assertFalse( ResultCache::hasResultWithHash( $request1->getHash() ) );//todo fix removing of cached results
		$this->assertTrue( ResultCache::hasResultWithHash( $request2->getHash() ) );
		$this->assertEquals( $request2->getResult(), ResultCache::getResultWithHash( $request2->getHash() ) );

		// clear the cache and assert neither result is there
		ResultCache::clearCachedResults();
		$this->assertFalse( ResultCache::hasResultWithHash( $request1->getHash() ) );//todo fix removing of cached results
		$this->assertFalse( ResultCache::hasResultWithHash( $request2->getHash() ) );//todo fix removing of cached results

	}

	function getRandomRequest(){
		$request = new ApiRequest( array( rand( 0, 99999999 ) ) );
		$request->setResult( array( 'Note' => 'This cached result was generated in a test' ) );
		return $request;
	}

}