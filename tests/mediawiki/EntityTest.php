<?php

namespace Addframe\Tests;

use Addframe\Entity;

/**
 *
 * @since 0.0.2
 *
 * @author Addshore
 */

class EntityTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @dataProvider provideValidConstructionValues
	 */
	function testCanConstructEntity( $site, $id, $new ){
		$entity = new Entity( $site , $id , $new );
		$this->assertTrue( true, 'Unable to construct a  Entity object with a url' );
		$this->assertEquals( strtolower( $id ), $entity->getId() );
	}

	function provideValidConstructionValues(){
		$values = array();
		$values[] = array( $this->getMockSite(), null , null );
		$values[] = array( $this->getMockSite(), 'q42' , null );
		$values[] = array( $this->getMockSite(), 'Q100' , null );
			//@todo add testing for new entities once implemented
		return $values;
	}

	function getMockSite(){
		return $this->getMockBuilder( 'Addframe\Site' )->disableOriginalConstructor()->getMock();
	}

	/**
	 * @dataProvider provideSitelinks
	 */
	function testCanAddSitelink( $site, $title ){
		$entity = $this->getDefaultEntity();
		$entity->addSitelink( $site, $title );
		$sitelinks = $entity->getLanguageData('sitelinks');
		$this->assertTrue( isset( $sitelinks[ $site ] ) );
		$this->assertEquals( array('site' => $site, 'title' => $title), $sitelinks[ $site ] );
	}

	function provideSitelinks(){
		$values = array();
		$values[] = array( 'enwiki', 'Wikipedia:Sandbox' );
		$values[] = array( 'ptwikivoyage', 'RegularArticle' );
		$values[] = array( 'zh_minwiki', 'M' );
		return $values;
	}

	function getDefaultEntity(){
		return new Entity( $this->getMockSite(), 'q42' );
	}

	/**
	 * @dataProvider provideDescriptions
	 */
	function testCanAddDescriptions( $lang, $description ){
		$entity = $this->getDefaultEntity();
		$entity->addDescription( $lang, $description );
		$descriptions = $entity->getLanguageData('descriptions');
		$this->assertTrue( isset( $descriptions[ $lang ] ) );
		$this->assertEquals( array('language' => $lang, 'value' => $description), $descriptions[ $lang ] );
	}

	function provideDescriptions(){
		$values = array();
		$values[] =  array( 'en', 'This is a description' );
		$values[] =  array( 'zh-min', 'And a UTF8 description?' );
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