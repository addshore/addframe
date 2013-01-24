<?

require 'parser.php';

class Page {

	// construct the page (you probably want to call load after this)
	public function __construct($page,$wiki) {
		$this->page = preg_replace("/_/","",$page);
		$this->parseNamespace();
		$this->wiki = $wiki;
	}	
	
	// variables
	private $page;// page name (e.g. "User:Addshore")
	private $text;// page text
	private $namespace;// page namespace (No colon)
	private $wiki;
	private $parser;// instance of the parser.php class
	private $sigchange = false;//has a significant change happened to the page (enough to edit)?
	private $summary;
	
	// getters and setters
	public function getName() { return $this->page; }
	public function getText() { if(!isset($this->text)){$this->loadText();} return $this->text;}
	public function getNamespace() { if(!isset($this->namespace)){$this->parseNamespace();} return $this->namespace;}
	public function hasSigchange() { return $this->sigchange; }
	
	// public functions
	public function parse() { $this->parser = new parser($this->page,$this->getText()); $this->parser->parse();} // create instance of parser class and parse
	
	// private functions
	private function loadText() { $this->text = $this->wiki->getpage($this->page);} // load the text from the wiki
	private function postPage() { $this->wiki->edit($this->page,$this->text,$this->summary,true);} // load the text from the wiki
	private function parseNamespace()
	{
		$result = preg_match("/^((User|Wikipedia|File|Image|Mediawiki|Template|Help|Category|Portal|Book|Education( |_)program|TimedText)(( |_)talk)?):?/i",$this->page,$matches);
		if($result == 0){ $this->namespace = "";}// default to article namespace
		else{$this->namespace = $matches[1];}
		if($this->namespace == "Image"){ $this->namespace = "File";}// default Image namespace to file
	}
	
	//adds an eddit to array summary
	private function addSummary($type,$what)
	{
		$this->sigchange = true;//if we have a summary it muse be a sig change
		$this->summary = $this->summary.$type." ".$what;
	}
	//forms the summary out of array
	public function getSummary()
	{
		return "[[User:Addbot|Bot:]] ".$this->summary."([[User talk:Addbot|Report Errors]])";
	}
	
	
//	                  //
// Main bot functions //
//                    //
	
	// returns false if not orphan
	public function isOrphan()
	{
		$links = $this->wiki->whatlinkshere($orphan,"&blnamespace=0");
		foreach($links as $link){
			if(preg_match("/((List|Index) of|\(disambig(uation)?\))/i",$link) == FALSE)// names to skip
			{
				if (preg_match("/(may refer to ?\:|# ?REDIRECT|\{\{Soft ?(Redir(ect)?|link)|\{\{.*((dis(amb?(ig(uation( page)?)?)?)?)(\-cleanup)?|d(big|ab|mbox)|sia|set index( articles)?).*\}\})/i",$this->wiki->getpage($link)) == FALSE)
				{return false;}
			}
		}
	}
	
	// returns false if page is not deadend
	public function isDeadend()
	{
		preg_match_all('/\[\[([a-z\/ _\(\)\|\.0-9]*)\]\]/i',$this->getText(), $links, PREG_PATTERN_ORDER);// match links to articles
		foreach($links[1] as $link){
			if(preg_match('/\|/',$link) != 0){
				$split = preg_split('/\|/',$link);// get the link rather than text
				$link = $split[0];
			}
			if (preg_match('/:/',$link) == 0){
				return false;			
			}
		}
	}
	
	public function isPdf()
	{ if( preg_match("/\.pdf$/i",$page->getName())) {return true; } }
	
	//remove the given template from the page
	public function removeTag($template)//passed $config['tag']['TEMPLATECODE'] (i.e. orphan)
	{
		$this->text = preg_replace($template->regexTemplate(),"",$this->getText());
		$this->addSummary("Removing",$template->getName());
	}
	
	//remove the given template from the page
	public function addTag($template,$section)//passed $config['tag']['TEMPLATECODE'] (i.e. orphan)
	{
		if($section)// if we want to add below a section
		{
			if(preg_match ("/== ?".$section." ?==/i",$this->text)) // if the section exists
			{
				$matches = preg_match ("/== ?".$section." ?==/i",$this->getText());
				$pieces = preg_split("/== ?".$section." ?==/i",$this->getText());
				$this->text = $pieces[0]."==".$matches[1]."==\n{{".$template."}} ".$pieces[1];
			}
			else // else it musant exist
			{
				$this->text = "==".$section."==\n{{BadFormat}}\n" .$this->getText();
			}
		}
		else// else just add it to the top
		{
			$this->text = "{{BadFormat}}\n" .$this->getText;
		}
		$this->addSummary("Adding",$template);
	}

}
	 
?>