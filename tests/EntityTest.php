<?php

namespace Addframe\Tests;

use Addframe\Entity;

class EntityTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @dataProvider provideValidConstructionValues
	 */
	function testCanConstructEntity( $values ){
		$entity = new Entity( $values[0] , $values[1] , $values[2] );
		$this->assertTrue( true, 'Unable to construct a  Entity object with a url' );
		$this->assertEquals( strtolower( $values[1] ), $entity->id );
	}

	function provideValidConstructionValues(){
		$values = array();
		$values[] = array( array( $this->getMockSite(), null , null ) );
		$values[] = array( array( $this->getMockSite(), 'q42' , null ) );
		$values[] = array( array( $this->getMockSite(), 'Q100' , null ) );
			//@todo add testing for new entities once implemented
		return $values;
	}

	function getMockSite(){
		return $this->getMockBuilder( 'Addframe\Site' )->disableOriginalConstructor()->getMock();
	}

	/**
	 * @dataProvider provideSitelinks
	 */
	function testCanAddSitelink( $values ){
		$entity = $this->getDefaultEntity();
		$entity->addSitelink( $values['site'], $values['title'] );
		$this->assertTrue( isset( $entity->languageData['sitelinks'][ $values['site'] ] ) );
		$this->assertEquals( array('site' => $values['site'], 'title' => $values['title']), $entity->languageData['sitelinks'][ $values['site'] ] );
	}

	function provideSitelinks(){
		$values = array();
		$values[] = array( array( 'site' => 'enwiki', 'title' => 'Wikipedia:Sandbox' ) );
		$values[] = array( array( 'site' => 'ptwikivoyage', 'title' => 'RegularArticle' ) );
		$values[] = array( array( 'site' => 'zh_minwiki', 'title' => 'M' ) );
		return $values;
	}

	function getDefaultEntity(){
		return new Entity( $this->getMockSite(), 'q42' );
	}

}