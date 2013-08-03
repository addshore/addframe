<?php

namespace Addframe\Mediawiki\Tests;
use Addframe\Mediawiki\Wikitext;

/**
 * @covers Addframe\Mediawiki\Wikitext
 *
 * @since 0.0.2
 *
 * @author Addshore
 */

class WikitextTest extends MediawikiTestCase {

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
		$wikiText = new Wikitext();
		$wikiText->setText( $string );
		$this->assertEquals( $string, $wikiText->getText(), "Wikitext Set Get roundtrip values did not match" );
	}

	/**
	 * @dataProvider provideString
	 */
	function testCanConstructWithString( $string ){
		$wikiText = new Wikitext( $string );
		$this->assertEquals( $string , $wikiText->getText(), "Failed to construct WikiText with a string" );
	}

	/**
	 * @dataProvider provideString
	 */
	function testCanAppendText( $string ){
		$start = "Starter String";
		$wikiText = new Wikitext($start);
		$wikiText->appendText($string);
		$this->assertEquals($start.$string, $wikiText->getText(), "Failed to append WikiText" );
	}

	/**
	 * @dataProvider provideString
	 */
	function testCanPrependText( $string ){
		$start = "Starter String";
		$wikiText = new Wikitext( $start );
		$wikiText->prependText($string);
		$this->assertEquals($string.$start, $wikiText->getText(), "Failed to prepend WikiText" );
	}

	/**
	 * @dataProvider provideString
	 */
	function textCanEmptyText( $string ){
		$wikiText = new Wikitext( $string );
		$wikiText->emptyText();
		$this->assertEquals("", $wikiText->getText(), "Failed to empty WikiText" );
	}

	/**
	 * @dataProvider provideString
	 */
	public function testGetLength( $string ){
		$wikiText = new Wikitext( $string );
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
		$wikiText = new Wikitext( $start );
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
		$wikiText = new Wikitext( $start );
		$wikiText->replaceString( $substring , $replacement );
		$this->assertEquals($result, $wikiText->getText(), "Failed to replace string");
	}

	//@todo extra text cases for regex

	public function testPregReplace() {
		$wikiText = new Wikitext("a string with the word b0t in it bot");
		$wikiText->pregReplace('/B0t/i', 'bot');
		$this->assertEquals("a string with the word bot in it bot", $wikiText->getText(), "Failed to preg replace string");
	}

	public function testRemoveRegexMatched() {
		$wikiText = new Wikitext("a string with the word b0t in it");
		$wikiText->removeRegexMatched('/B0t /i');
		$this->assertEquals("a string with the word in it", $wikiText->getText(), "Failed to remove regex match");
	}

	/**
	 * @dataProvider provideStringWithUrls
	 */
	public function testGeturls( $string, $expected ){
		$wikiText = new Wikitext( $string );
		$this->assertEquals( $expected, $wikiText->getUrls(), "Failed to correctly get Urls");
	}

	function provideStringWithUrls(){
		return array(
			array( "", array( ) ),
			array( "This is just a regular string", array( ) ),
			array( "https://meta.wikimedia.org/wiki", array( "https://meta.wikimedia.org/wiki" ) ),
			array( "ah8h8IFJKuihuifHUhiuyf8\nkjhIUFYhiuh\nhttp://www.google.com WOO\n.", array( "http://www.google.com" ) ),
			array( "ah8\nkjhI UFYhi uh\nhttp://tools.wmflabs.org/?status WOO\nDGJHkmf sggsg.", array( "http://tools.wmflabs.org/?status" ) ),
			array( "http://www.wikidata.org and //en.wikipedia.org", array( "http://www.wikidata.org", "//en.wikipedia.org" ) ),
			array( "http://", array( ) ),
		);
	}

	/**
	 * @dataProvider provideStringWithTrailingWhitespace
	 */
	public function testTrimWhitespace( $string, $expected ){
		$wikiText = new Wikitext( $string );
		$wikiText->trimWhitespace();
		$this->assertEquals( $expected, $wikiText->getText() );
	}

	function provideStringWithTrailingWhitespace(){
		return array(
			array( "a string\n", "a string\n" ),
			array( "a string\n\n\n", "a string\n\n" ),
			array( "a\n\n\nstring\n\n\n", "a\n\n\nstring\n\n" ),
			array( "a string\n\n\n", "a string\n\n" ),
			array( "\n\n\n\na string\n\n\n", "\n\n\n\na string\n\n" ),
		);
	}

	/**
	 * @dataProvider provideTextWithDateTagToFix
	 */
	public function testFixDateTags( $text, $result ){
		$wikiText = new Wikitext( $text );
		$wikiText->fixDateTags();
		$this->assertEquals( $result, $wikiText->getText() );
	}

	function provideTextWithDateTagToFix(){
		$date = '|date={{subst:CURRENTMONTHNAME}} {{subst:CURRENTYEAR}}';
		//We could add every possibility but thats just silly. So just have some.
		//If there is a reported problem with a specific template case, Add it as a test!
		$templateFamily = array(
			'Wikify' => array( 'Wikify', 'wikify-date', 'wfy' ),
			'Cleanup' => array( 'Cleanup', 'Clean up', 'CU' ),
			'Orphan' => array( 'Orphan', 'Linkless', ),
			'Unreferenced' => array( 'Unreferenced', 'add references' ),
			'Uncategorized' => array( 'Uncategorized', 'Classify', 'Category needed' ),
			'Trivia' => array( 'Trivia', 'Trivia2', ),
			'Deadend' => array( 'Deadend', 'DEP' ),
			'Copyedit' => array( 'Copyedit', 'copy-edit', 'cleanup-copyedit' ),
			'Refimprove' => array( 'refimprove', 'sources', 'not verified' ),
			'Expand' => array( 'Expand' ),
			'COI' => array( 'COI', 'Conflict of interest', 'Selfpromotion' ),
			'Intro missing' => array( 'Intro missing', 'No lead', 'No-Intro' ),
			'Primary sources' => array( 'Primary sources', 'reliablesources'),
		);
		$array = array(
			array( "", "" ),
			array( "A regular String Should remain\n", "A regular String Should remain\n" ),
			array( "A regular String {{orphan}} Should change\n", "A regular String {{Orphan".$date."}} Should change\n" ),
			array( "A regular String \n {{Template:wikify-date}}\n\n Should change\n", "A regular String \n {{Wikify".$date."}}\n\n Should change\n" )
		);
		foreach( $templateFamily as $mainTemplate => $templates ){
			foreach( $templates as $template ){
				$array[] = array( '{{'.$template.'}}', '{{'.$mainTemplate.$date.'}}' );
				$array[] = array( '{{Template:'.$template.'}}', '{{'.$mainTemplate.$date.'}}' );
			}
		}
		return $array;
	}

	//todo test cases for fixTemplates() (basically the same as above, just without date..
	//todo test cases for fixHTML()
	//todo test cases for fixHyperlinking()

}