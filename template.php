<?

class Template {

	// construct the page (you probably want to call load after this)
	public function __construct($name,$args,$redirects) {
		$this->name = $name;
		$this->args = $args;
		$this->redirects = $redirects;
		//TODO: Dynamically get redirects to this template
	}	
	
	// variables
	private $page;// template name e.g. Template:Orphan $page would be "Orphan"
	private $args;// template arguments
	private $redirects;// stores redirects to here

	//returns the regex for matching whole template and args
	public function regexTemplate() { return "/\{\{".$this->regexName.$this->regexArgs."\}\}/i"}
	//returns the regex for template name and redirects
	private function regexName() { return "(".$this->name."|".implode('|',$this->redirects.")";}
	//returns the regex for arguments
	private function regexArgs() { return "(|(".implode('|',$config['tag']['orphan']['parameters']).")( ?= ?[0-9a-z _])){0,".count($config['tag']['orphan']['parameters'])."}" }

}
	 
?>