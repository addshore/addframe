<?php

namespace Addframe\Tests;

use Addframe\WikiText;

/**
 * @covers Addframe\WikiText
 *
 * @since 0.0.3
 *
 * @author Addshore
 */

class WikiTextTest extends \PHPUnit_Framework_TestCase {

	function provideString(){
		return array(
			array( 'this is a string' ),
			array( '' ),
			array( '*()!"£$%^ A SlighTly more cOMPlex ;""""/.,<><> STRING!' ),
			array( "This\nis a string\nthat is split\nover\nmany\nmany\nlines\n\n\n" ),
		);
	}

	/**
	 * @dataProvider provideString
	 */
	function testCanSetAndGetText( $string ){
		$wikiText = new WikiText();
		$wikiText->setText( $string );
		$this->assertEquals( $string, $wikiText->getText(), "Wikitext Set Get roundtrip values did not match" );
	}

	/**
	 * @dataProvider provideString
	 */
	function testCanConstructWithString( $string ){
		$wikiText = new WikiText( $string );
		$this->assertEquals( $string , $wikiText->getText(), "Failed to construct WikiText with a string" );
	}

	/**
	 * @dataProvider provideString
	 */
	function testCanAppendText( $string ){
		$start = "Starter String";
		$wikiText = new WikiText($start);
		$wikiText->appendText($string);
		$this->assertEquals($start.$string, $wikiText->getText(), "Failed to append WikiText" );
	}

	/**
	 * @dataProvider provideString
	 */
	function testCanPrependText( $string ){
		$start = "Starter String";
		$wikiText = new WikiText( $start );
		$wikiText->prependText($string);
		$this->assertEquals($string.$start, $wikiText->getText(), "Failed to prepend WikiText" );
	}

	/**
	 * @dataProvider provideString
	 */
	function textCanEmptyText( $string ){
		$wikiText = new WikiText( $string );
		$wikiText->emptyText();
		$this->assertEquals("", $wikiText->getText(), "Failed to empty WikiText" );
	}

	/**
	 * @dataProvider provideString
	 */
	public function testGetLength( $string ){
		$wikiText = new WikiText( $string );
		$this->assertEquals( strlen( $string ) , $wikiText->getLength(), "Failed to get correct length" );
	}

	function provideStringReplacementAndResult(){
		return array(
			//start string,   substring,   does it exist?,   result
			array( 'a string with the word bot in it', 'bot', true, 'LOL' , 'a string with the word LOL in it' ),
			array( "\nStRiNg\n\n:)", 'StRiNg', true, '' ,"\n\n\n:)" ),
			array( "GFYghiuofjqohygah932", 'a stirng', false, 'Blub' ,"GFYghiuofjqohygah932" ),
		);
	}

	/**
	 * @dataProvider provideStringReplacementAndResult
	 */
	function testCanFindStringReturnsTrue( $start, $substring, $existed){
		$wikiText = new WikiText( $start );
		if( $existed ){
			$this->assertTrue( $wikiText->findString( $substring ), "Could not assert string was found" );
		} else {
			$this->assertFalse( $wikiText->findString( $substring ), "Could not assert string was not found" );
		}
	}

	/**
	 * @dataProvider provideStringReplacementAndResult
	 */
	public function testReplaceString(  $start, $substring, $existed, $replacement, $result ) {
		$wikiText = new WikiText( $start );
		$wikiText->replaceString( $substring , $replacement );
		$this->assertEquals($result, $wikiText->getText(), "Failed to replace string");
	}

	//@todo extra text cases for regex

	public function testPregReplace() {
		$wikiText = new WikiText("a string with the word b0t in it bot");
		$wikiText->pregReplace('/B0t/i', 'bot');
		$this->assertEquals("a string with the word bot in it bot", $wikiText->getText(), "Failed to preg replace string");
	}

	public function testRemoveRegexMatched() {
		$wikiText = new WikiText("a string with the word b0t in it");
		$wikiText->removeRegexMatched('/B0t /i');
		$this->assertEquals("a string with the word in it", $wikiText->getText(), "Failed to remove regex match");
	}

}