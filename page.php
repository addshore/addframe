<?

class Page {

	// construct the page (you probably want to call load after this)
	public function __construct($page) {
		$this->page = $page;
	}

	// page name (e.g. "User:Addshore")
	private $page;
	public string getName() { return $page; }
	
	// page text
	private $text;
	public string getText() { return $text;}
	public void loadText() { $this->text = $wiki->getpage($this->text);} // load the text from the wiki
	
	//removeTag($tag,$arguments)
	//addMultipleissues()

}
	 
?>