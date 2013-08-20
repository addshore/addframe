<?php

namespace Addframe\Mediawiki;

/**
 * @since 0.0.2
 * @author Addshore
 */

class TextContent {

	protected $text;
	protected $isSet;

	function __construct( $text = null) {
		$this->text = $text;
	}

	public function setText( $text ) {
		$this->text = $text;
	}

	public function getText() {
		return $this->text;
	}

	public function appendText( $text ) {
		$this->text = $this->text.$text;
	}

	public function prependText( $text ) {
		$this->text = $text.$this->text;
	}

	public function emptyText() {
		$this->text = "";
	}

	/**
	 * Find a string
	 * @param $string string The string that you want to find.
	 * @return bool (true or false)
	 **/
	public function findString( $string ) {
		if ( strstr( $this->text, $string ) )
			return true; else
			return false;
	}

	/**
	 * Replace a string
	 * @param $string string The string that you want to replace.
	 * @param $newstring string The string that will replace the present string.
	 */
	public function replaceString( $string, $newstring ) {
		$this->text = str_replace( $string, $newstring, $this->text );
	}

	public function pregReplace( $patern, $replacment ) {
		$this->text = preg_replace( $patern, $replacment, $this->text );
	}

	public function removeRegexMatched( $patern ) {
		$this->pregReplace( $patern, '' );
	}

	public function getLength(){
		return strlen( $this->text );
	}

	//todo: this will currently match things like "ssh://123.456.789.com", We probably want to avoid that...
	public function getUrls() {
		preg_match_all( Regex::getUrlRegex(), $this->text, $matches );
		return $matches[0];
	}

	public function trimWhitespace(){
		$this->pregReplace( '/(\n\n)\n+$/', "$1" );
		$this->pregReplace( '/^(\n|\r){0,5}$/', "" );
	}

	/**
	 * @original PeachyAWBFunctions class @ GNU General Public License
	 */
	public function fixCitations( ) {
		$text = $this->text;

		//merge all variant of cite web
		$text = preg_replace( '/\{\{\s*(cite[_ \-]*(url|web|website)|Web[_ \-]*(citation|cite|reference|reference[_ ]4))(?=\s*\|)/i', '{{cite web', $text );

		//Remove formatting on certian parameters
		$text = preg_replace( "/(\|\s*(?:agency|author|first|format|language|last|location|month|publisher|work|year)\s*=\s*)(''|'''|''''')((?:\[\[[^][|]+|\[\[|)[][\w\s,.~!`\"]+)(''+)(?=\s*\|[\w\s]+=|\s*\}\})/", '$1$3', $text );

		//Unlink PDF in format parameters
		$text = preg_replace( '/(\|\s*format\s*=\s*)\[\[(adobe|portable|document|file|format|pdf|\.|\s|\(|\)|\|)+\]\]/i', '$1PDF', $text );
		$text = preg_replace( '/(\|\s*format\s*=\s*)(\s*\.?(adobe|portable|document|file|format|pdf|\(|\)))+?(\s*[|}])/i', '$1PDF$4', $text );

		//No |format=HTML says {{cite web/doc}}
		$text = preg_replace( '/(\{\{cite[^{}]+)\|\s*format\s*=\s*(\[\[[^][|]+\||\[\[|)(\]\]| |html?|world|wide|web)+\s*(?=\||\}\})/i', '$1', $text );

		//Fix accessdate tags [[WP:AWB/FR#Fix accessdate tags]]
		$text = preg_replace(
			array(
				'/(\|\s*)a[ces]{3,8}date(\s*=\s*)(?=[^{|}]*20\d\d|\}\})/',
				'/accessdate(\s*=\s*)\[*(200\d)[/_\-](\d{2})[/_\-](\d{2})\]*/',
				'/(\|\s*)a[cs]*es*mou*nthday(\s*=\s*)/',
				'/(\|\s*)a[cs]*es*daymou*nth(\s*=\s*)/',
				'/(\|\s*)accessdate(\s*=\s*[0-3]?[0-9] +(?:Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)\w*)([^][<>}{]*accessyear[\s=]+20\d\d)/',
				'/(\|\s*)accessdate(\s*=\s*(?:Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)\w* +[0-3]?[0-9])([^][<>}{]*accessyear[\s=]+20\d\d)/',
				'/(\|\s*)accessdaymonth(\s*=\s*)\s*([^{|}<>]+?)\s*(\|[^][<>}{]*accessyear[\s=]+)(20\d\d)/',
				'/(\|\s*)accessmonthday(\s*=\s*)\s*([^{|}<>]+?)\s*(\|[^][<>}{]*accessyear[\s=]+)(20\d\d)/',
			),
			array(
				'$1accessdate$2',
				'accessdate$1$2-$3-$4',
				'$1accessmonthday$2',
				'$1accessdaymonth$2',
				'$1accessdaymonth$2$3',
				'$1accessmonthday$2$3',
				'$1accessdate$2$3 $5',
				'$1accessdate$2$3, $5',
			),
			$text );

		//Fix improper dates
		$text = preg_replace(
			array(
				'/(\{\{cit[ea][^{}]+\|\s*date\s*=\s*\d{2}[/\-.]\d{2}[/\-.])([5-9]\d)(?=\s*[|}])/i',
				'/(\{\{cit[ea][^{}]+\|\s*date\s*=\s*)(0[1-9]|1[012])[/\-.](1[3-9]|2\d|3[01])[/\-.](19\d\d|20\d\d)(?=\s*[|}])/i',
				'/(\{\{cit[ea][^{}]+\|\s*date\s*=\s*)(1[3-9]|2\d|3[01])[/\-.](0[1-9]|1[012])[/\-.](19\d\d|20\d\d)(?=\s*[|}])/i',
			),
			array(
				'${1}19$2',
				'$1$4-$2-$3',
				'$1$4-$3-$2',
			),

			$text );

		//Fix URLS lacking http://
		$text = preg_replace( '/(\|\s*url\s*=\s*)([0-9a-z.\-]+\.[a-z]{2,4}/[^][{|}:\s"]\s*[|}])/', '$1http://$2', $text );

		//Fix {{citation|title=[url title]}}
		$text = preg_replace( '/(\{\{cit[ea][^{}]*?)(\s*\|\s*)(?:url|title)(\s*=\s*)\[([^][<>\s"]*) +([^]\n]+)\](?=[|}])/i', '$1$2url$3$4$2title$3$5', $text );

		$this->setText( $text );

	}

	/**
	 * @original PeachyAWBFunctions class @ GNU General Public License
	 */
	public function fixDateTags( ) {
		$text = $this->text;

		//XX IF CHANGED ---->>> If you change ANY regex below please add test cases that test your change!
		$text = preg_replace( '/\{\{\s*(?:template:)?\s*(?:wikify(?:-date)?|wfy|wiki)(\s*\|\s*section)?\s*\}\}/iS', "{{Wikify$1|date={{subst:CURRENTMONTHNAME}} {{subst:CURRENTYEAR}}}}", $text );
		$text = preg_replace( '/\{\{(template:)?(Clean( ?up)?|CU|Tidy)\}\}/iS', "{{Cleanup|date={{subst:CURRENTMONTHNAME}} {{subst:CURRENTYEAR}}}}", $text );
		$text = preg_replace( '/\{\{(template:)?(Linkless|Orphan)\}\}/iS', "{{Orphan|date={{subst:CURRENTMONTHNAME}} {{subst:CURRENTYEAR}}}}", $text );
		$text = preg_replace( '/\{\{(template:)?(Unreferenced(sect)?|add references|cite[ -]sources?|cleanup-sources?|needs? references|no sources|no references?|not referenced|references|unref|unsourced)\}\}/iS', "{{Unreferenced|date={{subst:CURRENTMONTHNAME}} {{subst:CURRENTYEAR}}}}", $text );
		$text = preg_replace( '/\{\{(template:)?(Uncategori[sz]ed|Uncat|Classify|Category needed|Catneeded|categori[zs]e|nocats?)\}\}/iS', "{{Uncategorized|date={{subst:CURRENTMONTHNAME}} {{subst:CURRENTYEAR}}}}", $text );
		$text = preg_replace( '/\{\{(template:)?(Trivia2?|Too ?much ?trivia|Trivia section|Cleanup-trivia)\}\}/iS', "{{Trivia|date={{subst:CURRENTMONTHNAME}} {{subst:CURRENTYEAR}}}}", $text );
		$text = preg_replace( '/\{\{(template:)?(deadend|DEP)\}\}/iS', "{{Deadend|date={{subst:CURRENTMONTHNAME}} {{subst:CURRENTYEAR}}}}", $text );
		$text = preg_replace( '/\{\{(template:)?(copyedit|g(rammar )?check|copy-edit|cleanup-copyedit|cleanup-english)\}\}/iS', "{{Copyedit|date={{subst:CURRENTMONTHNAME}} {{subst:CURRENTYEAR}}}}", $text );
		$text = preg_replace( '/\{\{(template:)?(sources|refimprove|not verified)\}\}/iS', "{{Refimprove|date={{subst:CURRENTMONTHNAME}} {{subst:CURRENTYEAR}}}}", $text );
		$text = preg_replace( '/\{\{(template:)?(Expand)\}\}/iS', "{{Expand|date={{subst:CURRENTMONTHNAME}} {{subst:CURRENTYEAR}}}}", $text );
		//$text = preg_replace( '/\{\{(?:\s*[Tt]emplate:)?(\s*(?:[Cc]n|[Ff]act|[Pp]roveit|[Cc]iteneeded|[Uu]ncited|[Cc]itation needed)\s*(?:\|[^{}]+(?\<!\|\s*date\s*=[^{}]+))?)\}\}/iS', "{{$1|date={{subst:CURRENTMONTHNAME}} {{subst:CURRENTYEAR}}}}", $text );
		$text = preg_replace( '/\{\{(template:)?(COI|Conflict of interest|Selfpromotion)\}\}/iS', "{{COI|date={{subst:CURRENTMONTHNAME}} {{subst:CURRENTYEAR}}}}", $text );
		$text = preg_replace( '/\{\{(template:)?(Intro( |-)?missing|Nointro(duction)?|Lead missing|No ?lead|Missingintro|Opening|No-intro|Leadsection|No lead section)\}\}/iS', "{{Intro missing|date={{subst:CURRENTMONTHNAME}} {{subst:CURRENTYEAR}}}}", $text );
		$text = preg_replace( '/\{\{(template:)?([Pp]rimary ?[Ss]ources?|[Rr]eliable ?sources)\}\}/iS', "{{Primary sources|date={{subst:CURRENTMONTHNAME}} {{subst:CURRENTYEAR}}}}", $text );

		$this->setText( $text );
	}

	/**
	 * @original PeachyAWBFunctions class @ GNU General Public License
	 */
	public function fixTemplates( ) {
		$text = $this->text;

		//XX IF CHANGED ---->>> If you change ANY regex below please add test cases that test your change!
		$text = preg_replace( '/\{\{(?:Template:)?(Dab|Disamb|Disambiguation)\}\}/iS', "{{Disambig}}", $text );
		$text = preg_replace( '/\{\{(?:Template:)?(Bio-dab|Hndisambig)/iS', "{{Hndis", $text );
		$text = preg_replace( '/\{\{(?:Template:)?(Prettytable|Prettytable100)\}\}/iS', "{{subst:Prettytable}}", $text );
		$text = preg_replace( '/\{\{(?:[Tt]emplate:)?((?:BASE)?PAGENAMEE?\}\}|[Ll]ived\||[Bb]io-cats\|)/iS', "{{subst:$1", $text );
		$text = preg_replace( '/({{\s*[Aa]rticle ?issues\s*(?:\|[^{}]*|\|)\s*[Dd]o-attempt\s*=\s*)[^{}\|]+\|\s*att\s*=\s*([^{}\|]+)(?=\||}})/iS', "$1$2", $text );
		$text = preg_replace( '/({{\s*[Aa]rticle ?issues\s*(?:\|[^{}]*|\|)\s*[Cc]opyedit\s*)for\s*=\s*[^{}\|]+\|\s*date(\s*=[^{}\|]+)(?=\||}})/iS', "$1$2", $text );
		$text = preg_replace( '/\{\{[Aa]rticle ?issues(?:\s*\|\s*(?:section|article)\s*=\s*[Yy])?\s*\}\}/iS', "", $text );
		$text = preg_replace( '/\{\{[Cc]ommons\|\s*[Cc]ategory:\s*([^{}]+?)\s*\}\}/iS', "{{Commons category|$1}}", $text );
		$text = preg_replace( '/(?!{{[Cc]ite wikisource)(\{\{\s*(?:[Cc]it[ae]|[Aa]rticle ?issues)[^{}]*)\|\s*(\}\}|\|)/iS', "$1$2", $text );
		$text = preg_replace( '/({{\s*[Aa]rticle ?issues[^{}]*\|\s*)(\w+)\s*=\s*([^\|}{]+?)\s*\|((?:[^{}]*?\|)?\s*)\2(\s*=\s*)\3(\s*(\||\}\}))/iS', "$1$4$2$5$3$6", $text );
		$text = preg_replace( '/(\{\{\s*[Aa]rticle ?issues[^{}]*\|\s*)(\w+)(\s*=\s*[^\|}{]+(?:\|[^{}]+?)?)\|\s*\2\s*=\s*(\||\}\})/iS', "$1$2$3$4", $text );
		$text = preg_replace( '/(\{\{\s*[Aa]rticle ?issues[^{}]*\|\s*)(\w+)\s*=\s*\|\s*((?:[^{}]+?\|)?\s*\2\s*=\s*[^\|}{\s])/iS', "$1$3", $text );
		$text = preg_replace( '/{{\s*(?:[Cc]n|[Ff]act|[Pp]roveit|[Cc]iteneeded|[Uu]ncited)(?=\s*[\|}])/S', "{{Citation needed", $text );

		$this->setText( $text );
	}

	/**
	 * @original PeachyAWBFunctions class @ GNU General Public License
	 */
	public function fixHTML( ) {
		$text = $this->text;

		$text = preg_replace( '/(\n\{\| class="wikitable[^\n]+\n\|-[^\n]*)(bgcolor\W+CCC+|background\W+ccc+)(?=\W+\n!)/mi', '$1', $text );

		$text = preg_replace( '/(\n([^<\n]|<(?!br[^>]*>))+\w+[^\w\s<>]*)<br[ /]*>(?=\n[*#:;]|\n?<div|\n?<blockquote)/mi', '$1', $text );

		$text = preg_replace(
			array(
				'/(<br[^</>]*>)\n?</br>/mi',
				'/<[/]?br([^{/}<>]*?/?)>/mi',
				'/<br\s\S*clear\S*(all|both)\S*[\s/]*>/i',
				'/<br\s\S*clear\S*(left|right)\S*[\s/]*>/',
			),
			array(
				'$1',
				'<br$1>',
				'{{-}}',
				'{{clear$1}}'
			),
			$text
		);

		$text = preg_replace( '/(<font\b[^<>]*)> *\n?<font\b([^<>]*>)((?:[^<]|<(?!/?font))*?</font> *\n?)</font>/mi', '$1$2$3', $text );

		$text = preg_replace( '/<font ([^<>]*)>\[\[([^[\]{|}]+)\|([^[\]\n]*?)\]\]</font>/mi', '[[$2|<font $1>$3</font>]]', $text );

		$text = preg_replace( '/<font(( +style="[^"]+")+)>(?!\[\[)((?:[^<]|<(?!/?font))*?)(?<!\]\])</font>/mi', '<span$1>$3</span>', $text );

		$this->setText( $text );

	}

	/**
	 * @original PeachyAWBFunctions class @ GNU General Public License
	 */
	public function fixHyperlinking( ) {
		$text = $this->text;

		$text = preg_replace( '/(http:\/\/[^][<>\s"|])(&client=firefox-a|&lt=)(?=[][<>\s"|&])/', '$1', $text);

		$text = str_replace( '[{{SERVER}}{{localurl:', '[{{fullurl:', $text );

		$text = preg_replace( '/[(](?:see|) *(http:\/\/[^][<>"\s(|)]+[\w=\/&])\s?[)]/i', '<$1>', $text );

		$text = preg_replace( '/\[\[(https?:\/\/[^\]\n]+?)\]\]/', '[$1]', $text );
		$text = preg_replace( '/\[\[(https?:\/\/.+?)\]/', '[$1]', $text );

		$text = preg_replace( '/\[\[(:?)Image:([^][{|}]+\.(pdf|midi?|ogg|ogv|xcf))(?=\||\]\])/i', '[[$1File:$2', $text );

		$text = preg_replace(
			array(
				'/(http:\/* *){2,}(?=[a-z0-9:.\-]+\/)/i',
				"/(\[\w+:\/\/[^][<>\"\s]+?)''/i",
				'/\[\n*(\w+:\/\/[^][<>"\s]+ *(?:(?<= )[^\n\]<>]*?|))\n([^[\]<>{}\n=@\/]*?) *\n*\]/i',
				'/\[(\w+:\/\/[^][<>"\s]+) +([Cc]lick here|[Hh]ere|\W|→|[ -\/;-@]) *\]/i',
			),
			array(
				'http://',
				"$1 ''",
				'[$1 $2]',
				'$2 [$1]',
			),
			$text
		);

		$text = preg_replace( '/(\[\[(?:File|Image):[^][<>{|}]+)#(|filehistory|filelinks|file)(?=[\]|])/i', '$1', $text );

		$text = preg_replace( '/\[http://(www\.toolserver\.org|toolserver\.org|tools\.wikimedia\.org|tools\.wikimedia\.de)/([^][<>"\s;?]*)\?? ([^]\n]+)\]/', '[[tools:$2|$3]]', $text );

		$this->setText( $text );

	}
}