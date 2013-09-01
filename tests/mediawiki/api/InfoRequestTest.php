<?php

/**
 * Class InfoRequestTest
 * @covers Addframe\Mediawiki\Api\InfoRequest
 */
class InfoRequestTest extends MediawikiTestCase {

	function testCanConstruct( ){
		$class = '\Addframe\Mediawiki\Api\InfoRequest';
		$object = new $class();
		$this->assertInstanceOf( $class, $object );

	}

	function testInfoRequest(){
		$query = new \Addframe\Mediawiki\Api\InfoRequest();
		$params = $query->getParameters();
		$this->assertArrayHasKey( 'prop', $params );
		$this->assertEquals( 'info', $params['prop'] );
		$this->assertArrayHasKey( 'inprop', $params );
		$this->assertEquals( 'protection|talkid|watched|watchers|notificationtimestamp|subjectid|url|readable|preload|displaytitle', $params['inprop'] );
	}

}