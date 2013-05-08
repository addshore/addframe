<?
class WikiTextToHTML {
	/**
	 * Wiki text to HTML script.
	 * (c) 2007, Frank Schoep
	 *
	 * This script will convert the input given on the standard input
	 * from Wiki style text syntax to minimally formatted HTML output.
	 */

	// these constants define the list states
	const LS_NONE		=	0;
	const LS_ORDERED	=	1;
	const LS_UNORDERED	=	2;

	// definitions for the list open and close tags
	private static $LT_OPEN =
		array(
			LS_ORDERED	=>	"<ol>",
			LS_UNORDERED	=>	"<ul>"
		);

	private static $LT_CLOSE =
		array(
			LS_ORDERED	=>	"</ol>",
			LS_UNORDERED	=>	"</ul>"
		);
		
	// constants for defining preformatted code state
	const CS_NONE		=	0;
	const CS_CODE		=	1;

	/*
	 * These rules contain the conversion from Wiki text to HTML
	 * described as regular expressions. The first part matches
	 * source text, the second part rewrites it as HTML.
	 */	 
	private static $RULES =
		array(
			'/^= (.*) =$/'
				=>	'<h1>\1</h1>',
			'/^== (.*) ==$/'
				=>	'<h2>\1</h2>',
			'/^=== (.*) ===$/'
				=>	'<h3>\1</h3>',
			'/^==== (.*) ====$/'
				=>	'<h4>\1</h4>',
			'/^===== (.*) =====$/'
				=>	'<h5>\1</h5>',
			'/^====== (.*) ======$/'
				=> '<h6>\1</h6>',
			'/\[\[(.*?)\]\]/'
				=>	'<span class="keys">\1</span>',
			'/^([ ]+)1 (.+)$/'
				=>	'<li>\2</li>',
			'/^([ ]+)\* (.+)$/'
				=>	'<li>\2</li>',
			'/\*(.+?)\*/'
				=>	'<em>\1</em>',
			"/'''(.+?)'''/"
				=>	'<b>\1</b>',
			"/''(.+?)''/"
				=>	'<i>\1</i>',
			'/`(.+?)`/'
				=>	'<tt>\1</tt>',
			'/^----$/'
				=>	'<hr />'
		);
	
	/**
	 * Converts a Wiki text input string to HTML.
	 * 
	 * @param	array	$input	The array of strings containing Wiki
	 * 				text markup.
	 * @return	array	An array of strings containing the output
	 * 			in HTML.	
	 */
	public static function convertWikiTextToHTML($input) {
		// output array
		$output = array();

		// reset initial list states
		$liststate = LS_NONE;
		$listdepth = 1;
		$prevliststate = $liststate;
		$prevlistdepth = $listdepth;
		
		// preformatted code state
		$codestate = CS_NONE;

		// loop through the input
		foreach($input as $in) {
			// read, htmlify and right-trim each input line
			$in = htmlspecialchars(rtrim($in));
			$out = $in;		

			// match against Wiki text to HTML rules
			foreach(self::$RULES as $pattern => $replacement) {
				$out = preg_replace($pattern, $replacement,
					$out);
			}
	
			// determine list state based on leftmost character
			$prevliststate = $liststate;
			$prevlistdepth = $listdepth;
			switch(substr(ltrim($in), 0, 1)) {
				case '1':
					$liststate = LS_ORDERED;
					$listdepth = strpos($in, '1');
					break;
				case '*':
					$liststate = LS_UNORDERED;
					$listdepth = strpos($in, '*');
					break;
				default:
					$liststate = LS_NONE;
					$listdepth = 1;
					break;
			}
			
			// check if list state has changed
			if($liststate != $prevliststate) {
				// close old list
				if(LS_NONE != $prevliststate) {
					$output[] =
						self::$LT_CLOSE[$prevliststate];
				}
				
				// start new list
				if(LS_NONE != $liststate) {
					$output[] = self::$LT_OPEN[$liststate];
				}
			}
			
			// check if list depth has changed
			if ($listdepth != $prevlistdepth) {
				// calculate the depth difference
				$depthdiff = abs($listdepth - $prevlistdepth);

				// open or close tags based on difference
				if($listdepth > $prevlistdepth) {
					for($i = 0;
						$i < $depthdiff;
						$i++)
					{
						$output[] =
							self::$LT_OPEN[$liststate];
					}
				} else {
					for($i = 0;
						$i < $depthdiff;
						$i++)
					{
						$output[] =
							self::$LT_CLOSE[$prevliststate];
					}
				}
			}
			
			// determine output format
			if('' == $in) {
			} else if ('{{{' == trim($in)) {
				$output[] = '<p><pre><code>';
				$codestate = CS_CODE;
			} else if ('}}}' == trim($in)) {
				$output[] = '</code></pre></p>';
				$codestate = CS_NONE;
			} else if (
				$in[0] != '=' &&
				$in[0] != ' ' &&
				$in[0] != '-')
			{
				// only output paragraphs when not in code
				if(CS_NONE == $codestate) {
					$output[] = '<p>';
				}

				$output[] = $out;

				// only output paragraphs when not in code
				if(CS_NONE == $codestate) {
					$output[] = '</p>';
				}
			} else {
				$output[] = $out;
			}
		}
		
		// return the output
		return $output;
	}

	/**
	 * Converts an input stream to HTML.
	 * 
	 * @param	stream	$input	The input stream containing lines
	 * 				of Wiki text markup.
	 * @return	array	An array of strings containing the output
	 * 			in HTML.
	 */
	public static function convertWikiTextStreamToHTML($stream) {
		// input buffer
		$input = array();
		
		// loop through the stream
		while(!feof($stream)) {
			$input[] = fgets($stream);
		}
		
		// convert Wiki text to HTML and return result
		return self::convertWikiTextToHTML($input);
	}
}
?>