<?

require 'parser.php';

class Page {

	// construct the page (you probably want to call load after this)
	public function __construct($page) {
		$this->page = $page;
		parseNamespace();
	}	
	
	// variables
	private $page;// page name (e.g. "User:Addshore")
	private $text;// page text
	private $namespace;// page namespace (No colon)
	private $parser;// instance of the parser.php class
	
	// getters and setters
	public string getName() { return $page; }
	public string getText() { if(!isset($text)){loadText();} return $text;}
	public string getNamespace() { if(!isset($namespace)){parseNamespace();} return $namespace;}
	
	// public functions
	public void parse() { $parser = new parser($page,getText()); $parser->parse();} // create instance of parser class and parse
	
	// private function
	private void loadText() { $this->text = $wiki->getpage($page);} // load the text from the wiki
	private void parseNamespace()
	{
		preg_match("/^((User|Wikipedia|File|Image|Mediawiki|Template|Help|Category|Portal|Book|Education( |_)program|TimedText)(( |_)talk)?):?/i",$page,$matches);
		$this->namespace = $matches[1];
	}
	
	//removeTag($tag,$arguments)
	//addMultipleissues()

}
	 
?>