<?
require 'parser.php';

class Page {

	// construct the page (you probably want to call load after this)
	public function __construct($page,$wiki) {
		$this->page = preg_replace("/_/"," ",$page);
		$this->wiki = $wiki;
		$this->parseNamespace();
		$this->loadText();//load the wikitext from page
	}	
	
	// variables
	private $page;// page name (e.g. "User:Addshore")
	private $text;// page text
	private $namespace;// page namespace (No colon)
	private $wiki;// instance of wiki we are using
	private $parser;// instance of the parser.php class
	private $parsed;
	private $sigchange;//has a significant change happened to the page (enough to edit)?
	private $summary;//summary if edited
	
	// getters and setters
	public function getName() { return $this->page; }
	public function getText() { return $this->text;}
	public function getNamespace() { if(!isset($this->namespace)){$this->parseNamespace();} return $this->namespace;}
	public function hasSigchange() { return $this->sigchange; }
	
	// public functions
	// create instance of parser class and parse
	public function parse() { $this->parser = new parser($this->getName(),$this->getText()); $this->parsed = $this->parser->parse(); return $this->parsed;} 
	
	// private functions
	private function loadText() { $this->text = $this->wiki->getpage($this->getName());} // load the text from the wiki
	private function postPage() { $this->wiki->edit($this->getName(),$this->getText(),$this->getSummary(),true);} 
	private function parseNamespace()
	{
		$result = preg_match("/^((User|Wikipedia|File|Image|Mediawiki|Template|Help|Category|Portal|Book|Education( |_)program|TimedText)(( |_)talk)?):?/i",$this->page,$matches);
		if($result == 0){ $this->namespace = "";}// default to article namespace
		else{$this->namespace = $matches[1];}
		if($this->namespace == "Image"){ $this->namespace = "File";}// default Image namespace to file
	}
	
	private function addSummary($sum)
	{
		//only add the first bit if it is not already there (i.e. 'Adding' or 'Removing')
		$split = explode(" ",$sum,2);
		if(!preg_match('/'.$split[0].'/i',$this->summary))
		{
			$this->summary = $this->summary.$split[0]." ";
		}
		$this->summary = $this->summary.$split[1]." ";
		
		$this->sigchange = true;//if we have a summary it muse be a sig change
	}
	
	//returns the edit summary
	public function getSummary(){
	return "[[User:Addbot|Bot:]] - ".$this->summary."([[User talk:Addbot|Report Errors]] 2)";
	}
	
//	                  //
// Main bot functions //
//                    //

	//make matching easier
	public function matches($regex){return preg_match($regex,$this->getText());}
	
	//return a restricted estimate of words in an article
	public function wordcount()
	{
		//get a temp copy of the text to work with
		$text = $this->getText();
		//remove templates, cats, interwikis and extlinks and refs
		$text = preg_replace("/(\{\{[^\}]*?\}\}|={1,6}[^=]*?={1,6}|\n\*{1,2} ?|\[https?[^\]]*?\]|\[\[(Category|Image|File|[a-z]{2,6}):[^\]]*?\]\]|\<references ?\/\>|<ref>.*?<\/ref>|<!--.*?-->)/is","",$text);
		//fill all links in with a single word
		$text = preg_replace("/\[\[[^\]]*?\]\]/","WORD",$text);
		$text = trim($text);
		//return
		return str_word_count($text);
	}
	
	// returns false if the largest section size is smaller than 5000 chars (excluding certain sections)
	public function needsSections()
	{
		//init some vars
		$largestsection = 0;
		$sectioncount = 0;
		//find the sections
		preg_match_all('/\n==(=)? ?.* ?===?/i',$text, $sections, PREG_PATTERN_ORDER);
		$split = preg_split('/\n==(=)? ?.* ?===?/i',$text);
			
		//for each section found
		foreach($split as $id => $section){
			//if we are the lead
			if($id == 0){
				$largestsection = strlen($section);
				$sectioncount++;
			}
			//else we must have a name
			else{
				//make sure we ignore the sections below
				if (preg_match('/See ?also|(external( links)?|references|notes|bibliography|further( reading)?)/i',$sections[0][$id-1]) == 0){
					//if the length of this section is longer than our current largest
					if(strlen($section) > $largestsection){
						//then set it
						$largestsection = strlen($section);
					}
				//increment the section count
				$sectioncount++;
				}
			}
		}
		//if the page has 4+ sections and a largest section of 5000- then return false
		if($sectioncount >= $config['Sections']['sections'] && $largestsection <= $config['Sections']['largest']){//was 2750 for AVG
			return false;
		}
	}
	
	//returns true if we have a <ref tag
	public function isReferenced()
	{
		//if we match a ref tag
		if($this->matches('/<\/?ref[^\/]*?>/is'))
		{
			return true;
		}
		return null;
	}
	
	// returns true if there are 0 links to the page from the mainspace
	// returns false if there is at least 1 link that fits the criteria
	public function isOrphan()
	{
		//get the links to the page
		$links = $this->wiki->whatlinkshere($this->getName(),"&blnamespace=0");
		//if no links return as IS ORPHAN
		if(count($links) == 0) {return true;}
		//if there are links then check them
		foreach($links as $link){
			//regex names of links to ignore
			if(preg_match("/((List|Index) of|\(disambig(uation)?\))/i",$link) == FALSE)
			{
				//regex of contents of pages to ignore
				if (preg_match("/(may refer to ?\:|# ?REDIRECT|\{\{Soft ?(Redir(ect)?|link)|\{\{.*((dis(amb?(ig(uation( page)?)?)?)?)(\-cleanup)?|d(big|ab|mbox)|sia|set index( articles)?).*\}\})/i",$this->wiki->getpage($link)) == FALSE)
				//if we got this far it isnt an orphaned page
				{return false;}
			}
		}
		return null;
	}
	
	// returns false if one blue link is found on the page
	// returns true if 0 links are found
	public function isDeadend()
	{
		// match links to articles
		preg_match_all('/\[\[([a-z\/ _\(\)\|\.0-9]*)\]\]/i',$this->getText(), $links, PREG_PATTERN_ORDER);
		foreach($links[1] as $link){
			//if this link has been renammed i.e. [[User:Addbot|Bot]]
			if(preg_match('/\|/',$link) != 0){
				// get the link rather than text name
				$split = preg_split('/\|/',$link);
				$link = $split[0];
			}
			if (preg_match('/:/',$link) == 0){
				return false;			
			}
		}
		if(count($links) == 0){ return true; }
	}
	
	// returns true is 0 categories are found
	// returns false if more than one is found
	public function isUncat()
	{
		// get cats for this page
		$cats = $this->wiki->categories($this->getName(),false);
		// tag as apropriate
		if(count($cats) == 0){return true;}else{return false;}
	}
	
	//return true if the page is appended by .pdf
	public function isPdf()
	{ 
		if( preg_match("/\.pdf$/i",$this->getName()))
		{
			return true; 
		} 
	}
	
	//add the given template from the page if it doesnt already exist
	//passed $config['tag']['TEMPLATECODE'] (i.e. orphan)
	public function addTag($template,$section=null)
	{
		//make sure the tag is not already on the page
		if(preg_match($template->regexTemplate(),$this->getText()) || preg_match($template->regexTempIssues(),$this->getText())){ return false; }
		//check if we want to add the tag below a section
		if($section)
		{
			//does the section exist?
			if(preg_match ("/== ?".$section." ?==/i",$this->text))
			{
				//then add the tag
				$matches = preg_match ("/== ?".$section." ?==/i",$this->getText());
				$pieces = preg_split("/== ?".$section." ?==/i",$this->getText());
				$this->text = $pieces[0]."==".$matches[1]."==\n".$template->getPost()." ".$pieces[1];
			}
			else // else we can just make the section
			{
				$this->text = "==".$section."==\n".$template->getPost()."\n" .$this->getText();
			}
		}
		else// else just add it to the top of the page
		{
			$this->text = $template->getPost()."\n" .$this->getText();
		}
		// add to the summary for the edit
		$this->addSummary("Adding {{".$template->getName()."}}");
	}
	
	//passed $config['tag']['TEMPLATECODE'] (i.e. orphan)
	public function removeTag($template)
	{
		$this->removeRegex($template->regexTemplate(),"Removing {{".$template->getName()."}}");
	}
	
	//remove the regex match from the page
	//if cummary is set then add to edit summary
	public function removeRegex($regex,$summary = null)
	{
		if(preg_match($regex,$this->getText()))//make sure the regex is actually there
		{//if it is remove and say so
			$this->text = preg_replace($regex,"",$this->getText());
			if($summary != null)
			{//if summary not null then we can add a summary
				$this->addSummary($summary);
			}
		}
	}
	
	//parse MI tag, add tags to MI, remove MI if not needed
	public function multipleIssues()
	{
		global $config;
		$removed = 0;
		$hat = "";//for storing nay hat notes in
		$mi = "";//this will be used to store what we want to add to the page
		//parse the page
		$this->parse(); // work with $this->parsed;
		//for each template on the page
		foreach($this->parsed['wikObject_templates'] as $x)
		{
			//make sure the template is not surrounded by comment tags
			if(!preg_match('/<!--.*?'.preg_quote($x->rawCode).'.*?-->/is',$this->getText()))
			{
			
				//does it match the MI template
				if(preg_match('/^(Multiple issues|Article issues|Issues|MI|Many Issues|Multiple|Multipleissues)/i',$x->name))
				{
					//does it match the old style of use
					if(preg_match('/\{\{(multiple ?issues|article ?issues|mi)\s*\|([^{]+)\}\}/i',$x->rawCode))
					{
						//then parse accordingly
						foreach($x->arguments[1] as $tagarg)
						{
							$mi = $mi."{{".trim(preg_replace('/ ?= ?/','|date=',$tagarg))."}}\n";
						}
					}
					else//else we must be a new MI style
					{
						//the parse accordingly
						$mi = $mi.$x->arguments[1];
						$removed = $removed + $x->attributes['length'];
						$this->text = substr($this->getText(),"",$x->attributes['start']-$removed,$x->attributes['length']);
					}
				}
				//else do we match any hatnotes
				elseif(preg_match('/\{\{(Template:)?(Hatnote|Reflink|Main(( |_)list)?|Details3?|See( |_)also2?|Further2?|About|Other( |_)uses-section|for|((Two|Three) )?Other( |_)uses|Other uses of|Redirect[0-1]?[0-9]|Redirect(-|_| )(synomym|text|distinguish2?)|Consider( |_)disambiguation|Other( |_)(uses|people|places|hurricanes|ships|)[1-5]?|(Redirect-)Distinguish|Selfref|Category( |_)(see also|explanation|pair)|Cat( |_)main|cat(preceding|succeeding)|contrast|This( |_)user( |_)talk)(\||\}\})/i',$x->name))
				{
					//remember our hatnotes 
					$hat = $hat.$x->rawCode."\n";
					//remove the hatnote matched (we will re add later)
					$this->text = substr($this->getText(),"",$x->attributes['start']-$removed,$x->attributes['length']);
				}
				else// else if we match a tag to go in MI
				{
					//check for all of our defined tags
					foreach($config['tag'] as $tag)
					{
						//if it is one of our tags
						if(preg_match("/^".$tag->regexName()."$/i",$x->name) == true)
						{
							//if we have a section param ignore the tag
							if(preg_match("/\|(sections|sect?)/i",$x->rawCode) == false)
							{
								//remove the tag from page and add to our output
								$mi = $mi.$x->rawCode;
								$this->text = substr_replace($this->getText(),"",$x->attributes['start']-$removed-1,$x->attributes['length']);
								$removed = $removed + $x->attributes['length'];
							}
						}
					}
				}
			}
		}
		//crappy way to make sure we split at every tag
		$mi = preg_replace('/\}\}/',"}}\n",$mi);
		//split into each tag (might be joined if from MI)
		$split = preg_split("/\n/",$mi,0,PREG_SPLIT_NO_EMPTY);
		//If there is at least 2 tags
		if(count($split) > 1)
		{
			//add them to a MI tag
			$mi = "{{Multiple issues|\n";//start mi
			foreach ($split as $tag)
			{
				$mi = $mi.$tag."\n";//add each tag
			}
			$mi = $mi."}}";//add the end of the tag
		}
		//if only 1 we dont want to use multiple issues
		elseif(count($split) == 1)
		{
			//just add the single tag
			$mi = $split[0];
		}
		else
		{
			//we actually dont have any tags 
			return false;
		}

		//add to origional text with any hatnotes
		$this->text = $hat.$mi."\n".$this->getText();

	}
	
	//http://en.wikipedia.org/w/index.php?title=Wikipedia:AutoEd/whitespace.js&action=raw&ctype=text/javascript
	public function fixWhitespace()
	{
		$this->text = preg_replace('/\t/'," ", $this->getText() );
		
		$this->text = preg_replace('/^ ? ? \n/m',"\n", $this->getText() );
		$this->text = preg_replace('/(\n\n)\n+/',"$1", $this->getText() );
		$this->text = preg_replace('/== ? ?\n\n==/',"==\n==", $this->getText() );
		$this->text = preg_replace('/\n\n(\* ?\[?http)/',"\n$1", $this->getText() );
		
		$this->text = preg_replace('/^ ? ? \n/m',"\n", $this->getText() );
		$this->text = preg_replace('/\n\n\*/',"\n*", $this->getText() );
		$this->text = preg_replace('/([=\n]\n)\n+/',"$1", $this->getText() );
		$this->text = preg_replace('/ \n/',"\n", $this->getText() );
		
		$this->text = preg_replace('/^([\*#]+:*) /m',"$1", $this->getText() );
		$this->text = preg_replace('/^([\*#]+:*)/m',"$1 ", $this->getText() );
		
		$this->text = preg_replace('/^(={1,4} )[ ]*([^= ][^=]*[^= ])[ ]*( ={1,4})$/m',"$1$2$3", $this->getText() );
		$this->text = preg_replace('/^(={1,4})([^= ][^=]*[^= ])[ ]+(={1,4})$/m',"$1$2$3", $this->getText() );
		$this->text = preg_replace('/^(={1,4})[ ]+([^= ][^=]*[^= ])(={1,4})$/m',"$1$2$3", $this->getText() );
	}
	
	public function fixTemplates()
	{
		$this->text = preg_replace( '/\{\{(?:Template:)?(Dab|Disamb|Disambiguation)\}\}/iS', "{{Disambig}}", $this->text );
		$this->text = preg_replace( '/\{\{(?:Template:)?(Bio-dab|Hndisambig)/iS', "{{Hndis", $this->text );
		$this->text = preg_replace( '/\{\{(?:Template:)?(Prettytable|Prettytable100)\}\}/iS', "{{subst:Prettytable}}", $this->text );
		$this->text = preg_replace( '/\{\{(?:[Tt]emplate:)?((?:BASE)?PAGENAMEE?\}\}|[Ll]ived\||[Bb]io-cats\|)/iS', "{{subst:$1", $this->text );
		$this->text = preg_replace( '/({{\s*[Aa]rticle ?issues\s*(?:\|[^{}]*|\|)\s*[Dd]o-attempt\s*=\s*)[^{}\|]+\|\s*att\s*=\s*([^{}\|]+)(?=\||}})/iS', "$1$2", $this->text );
		$this->text = preg_replace( '/({{\s*[Aa]rticle ?issues\s*(?:\|[^{}]*|\|)\s*[Cc]opyedit\s*)for\s*=\s*[^{}\|]+\|\s*date(\s*=[^{}\|]+)(?=\||}})/iS', "$1$2", $this->text );
		$this->text = preg_replace( '/\{\{[Aa]rticle ?issues(?:\s*\|\s*(?:section|article)\s*=\s*[Yy])?\s*\}\}/iS', "", $this->text );
		$this->text = preg_replace( '/\{\{[Cc]ommons\|\s*[Cc]ategory:\s*([^{}]+?)\s*\}\}/iS', "{{Commons category|$1}}", $this->text );
		$this->text = preg_replace( '/(?!{{[Cc]ite wikisource)(\{\{\s*(?:[Cc]it[ae]|[Aa]rticle ?issues)[^{}]*)\|\s*(\}\}|\|)/iS', "$1$2", $this->text );
		$this->text = preg_replace( '/({{\s*[Aa]rticle ?issues[^{}]*\|\s*)(\w+)\s*=\s*([^\|}{]+?)\s*\|((?:[^{}]*?\|)?\s*)\2(\s*=\s*)\3(\s*(\||\}\}))/iS', "$1$4$2$5$3$6", $this->text );
		$this->text = preg_replace( '/(\{\{\s*[Aa]rticle ?issues[^{}]*\|\s*)(\w+)(\s*=\s*[^\|}{]+(?:\|[^{}]+?)?)\|\s*\2\s*=\s*(\||\}\})/iS', "$1$2$3$4", $this->text );
		$this->text = preg_replace( '/(\{\{\s*[Aa]rticle ?issues[^{}]*\|\s*)(\w+)\s*=\s*\|\s*((?:[^{}]+?\|)?\s*\2\s*=\s*[^\|}{\s])/iS', "$1$3", $this->text );
		$this->text = preg_replace( '/{{\s*(?:[Cc]n|[Ff]act|[Pp]roveit|[Cc]iteneeded|[Uu]ncited)(?=\s*[\|}])/S', "{{Citation needed", $this->text );
	}
	
	public function fixDateTags()
	{
		global $config;
		//get a copy of the text to change
		$text = $this->getText();
		//get the current month and year
		$date = date("F Y");
		
		//check each tag we have to see if it needs to be dated
		foreach ($config['tag'] as $tag)
		{
			//if the tag can be found without a date
			if($this->matches('/\{\{(Template:)?'.$tag->regexName().'\}\}/i'))
			{
				//then date it
				$text = preg_replace('/\{\{(Template:)?'.$tag->regexName().'\}\}/i',"{{".$tag->getName()."|date=$date}}",$text);
			};
		}
		
		//If a tag has been dated
		if(strlen($text) > strlen($this->getText())+5)
		{
			$this->text = $text;
			echo "+";
			$this->addSummary("Dating Tags");
		}
	}
	
	public function fixGeneral()
	{
		//Fix headers
		$this->text = preg_replace('/== ?External ?links? ?==/i', "==External links==", $this->text );
		$this->text = preg_replace('/== ?Further ?readings? ?==/i', "==Further reading==", $this->text );
		//Unicode
		$this->text = preg_replace('/&mdash/i', "—", $this->text );
		$this->text = preg_replace('/&ndash/i', "–", $this->text );
		//Templates
		$this->text = preg_replace('/\{\{Unreferenced\|section\}\}/i', "{{Unreferenced section}}", $this->text );
		$this->text = preg_replace('/<references \/>/i',"{{reflist}}", $this->text );
	
	}
}
	 
?>