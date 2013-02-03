<?

class Template {

	// construct the page (you probably want to call load after this)
	public function __construct($name,$redirects,$dated=false) {
		$this->name = $name;
		$this->redirects = $redirects;
		$this->dated = $dated;
	}	
	
	// variables
	private $name;// template name e.g. Template:Orphan $page would be "Orphan"
	private $redirects;// stores redirects to here
	private $dateregex = '((January|February|March|April|May|June|July|August|September|October|November|December) ?20[0-9][0-9])';

	public function getName() { return $this->name; } //returns the name of the template
	//get an instance of the template to post
	public function getPost() {
	//do we want to return with a date
	if($this->dated)
	{
		$date = date("F Y");
		return "{{".$this->getName()."|date=$date}}";
	}
	//or not
	return "{{".$this->getName()."}}";
	}
	
	//returns the regex for matching whole template and args
	public function regexTemplate() { return '/\{\{'.$this->regexName().$this->regexArgs().'\}\}(\r|\n){0,3}/i'; }
	public function regexTempIssues() { return '/\| ?'.$this->regexName().' ?= ?'.$this->dateregex.'(\r|\n){0,1}/i'; }
	//returns the regex for template name and redirects
	public function regexName() {
	$string = '('.$this->name."|".implode('|',$this->redirects).')';
	return preg_replace("/(\|\||\|\))/",")",$string);//remove any extram room
	}
	//returns the regex for arguments
	private function regexArgs() { return '(\|([0-9a-zA-Z _]*?)( ?= ?[0-9a-zA-Z _]*?)){0,6}'; }

}
	 
?>