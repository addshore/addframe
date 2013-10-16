<?php

namespace Addframe\Test\Unit;

use Addframe\Mediawiki\Api\InfoRequest;

/**
 * Class InfoRequestTest
 * @covers Addframe\Mediawiki\Api\InfoRequest
 */
class InfoRequestTest extends MediawikiTestCase {

	public function testCanConstruct( ){
		$class = '\Addframe\Mediawiki\Api\InfoRequest';
		$object = new $class();
		$this->assertInstanceOf( $class, $object );

	}

	public function testInfoRequest(){
		$query = new InfoRequest();
		$params = $query->getParameters();
		$this->assertArrayHasKey( 'prop', $params );
		$this->assertEquals( 'info', $params['prop'] );
		$this->assertArrayHasKey( 'inprop', $params );
		$this->assertEquals( 'protection|talkid|watched|watchers|notificationtimestamp|subjectid|url|readable|preload|displaytitle', $params['inprop'] );
	}

}