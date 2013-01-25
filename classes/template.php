<?

class Template {

	// construct the page (you probably want to call load after this)
	public function __construct($name,$redirects,$args) {
		$this->name = $name;
		$this->args = $args;
		$this->redirects = $redirects;
		//TODO: Dynamically get redirects to this template
	}	
	
	// variables
	private $name;// template name e.g. Template:Orphan $page would be "Orphan"
	private $args;// template arguments
	private $redirects;// stores redirects to here
	private $dateregex = '((January|February|March|April|May|June|July|August|September|October|November|December) ?20[0-9][0-9])';

	public function getName() { return $this->name; } //returns the name of the template
	
	//returns the regex for matching whole template and args
	public function regexTemplate() { return '/\{\{'.$this->regexName().$this->regexArgs().'\}\}(\r|\n){0,3}/i'; }
	public function regexTempIssues() { return '/\| ?'.$this->regexName().' ?= ?'.$this->dateregex.'(\r|\n){0,1}/i'; }
	//returns the regex for template name and redirects
	private function regexName() { return '('.$this->name.'|'.implode('|',$this->redirects).')'; }
	//returns the regex for arguments
	private function regexArgs() { return '(\|('.implode('|',$this->args).')( ?= ?[0-9a-z _]*?)){0,'.count($this->args).'}'; }

}
	 
?>