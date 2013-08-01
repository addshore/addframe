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
		$this->assertEquals( strtolower( $values[1] ), $entity->getId() );
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
		$sitelinks = $entity->getLanguageData('sitelinks');
		$this->assertTrue( isset( $sitelinks[ $values['site'] ] ) );
		$this->assertEquals( array('site' => $values['site'], 'title' => $values['title']), $sitelinks[ $values['site'] ] );
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

	/**
	 * @dataProvider provideDescriptions
	 */
	function testCanAddDescriptions( $values ){
		$entity = $this->getDefaultEntity();
		$entity->addDescription( $values['lang'], $values['value'] );
		$descriptions = $entity->getLanguageData('descriptions');
		$this->assertTrue( isset( $descriptions[ $values['lang'] ] ) );
		$this->assertEquals( array('language' => $values['lang'], 'value' => $values['value']), $descriptions[ $values['lang'] ] );
	}

	function provideDescriptions(){
		$values = array();
		$values[] = array( array( 'lang' => 'en', 'value' => 'This is a description' ) );
		$values[] = array( array( 'lang' => 'zh-min', 'value' => 'And a UTF8 description?' ) );
		return $values;
	}

	//@todo test add alias/aliases
	//@todo test set sitelink
	//@todo test set description
	//@todo test set aliases
	//@todo test remove sitelink
	//@todo test remove description
	//@todo test remove aliases


}