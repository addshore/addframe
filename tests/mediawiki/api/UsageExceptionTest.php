<?php

/**
 * Class UsageExceptionTest
 * @covers Addframe\Mediawiki\Api\UsageException
 */
class UsageExceptionTest extends MediawikiTestCase {

	function throwUsageException( $array ){
		throw new Addframe\Mediawiki\Api\UsageException( $array );
	}

	function testUsageException(){
		$array = array( 'code' => 'imacode', 'info' => 'imsomeinfo' );

		try{
			$this->throwUsageException( $array );
		} catch( Addframe\Mediawiki\Api\UsageException $e ){
			$this->assertInstanceOf( 'Addframe\Mediawiki\Api\UsageException', $e );
			$this->assertEquals( $array['code'], $e->getCodeString() );
			$this->assertEquals( $array, $e->getMessageArray() );
			$this->assertContains( $array['code'], $e->__tostring() );
			$this->assertContains( $array['info'], $e->__tostring() );
		}

	}

}