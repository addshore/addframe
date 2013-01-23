<?

require 'parser.php';

class Page {

	// construct the page (you probably want to call load after this)
	public function __construct($page) {
		$this->page = $page;
	}
	
	// variables
	private $page;// page name (e.g. "User:Addshore")
	private $text;// page text
	private $parser;// instance of the parser.php class
	
	// getters and setters
	public string getName() { return $page; }
	public string getText() { return $text;} //TODO: if empty get text
	
	// functions
	public void loadText() { $this->text = $wiki->getpage($this->text);} // load the text from the wiki
	public void parse() { $parser = new parser($page,getText()); $parser->parse();} // create instance of parser class and parse
	//removeTag($tag,$arguments)
	//addMultipleissues()

}
	 
?>