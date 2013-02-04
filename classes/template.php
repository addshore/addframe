<?

class Template {

	// construct the page (you probably want to call load after this)
	public function __construct($name,$redirects,$dated=false,$notif=null) {
		$this->name = $name;
		$this->redirects = $redirects;
		$this->dated = $dated;
		$this->notif = $notif;
	}	
	
	//Variables
	private $name;// template name e.g. Template:Orphan $page would be "Orphan"
	private $redirects;// stores redirects to here
	private $dated;//stores is the template tags a date arg
	private $notif;//dont use this if the templates in this array are on the page
	private $dateregex = '((January|February|March|April|May|June|July|August|September|October|November|December) ?20[0-9][0-9])';

	//Datas
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
	
	//Regexs
	//returns the regex for matching whole template and args
	public function regexTemplate() {
		return '/\{\{'.$this->regexName().$this->regexArgs().'\}\}(\r|\n){0,3}/i';
	}
	
	//matches in the old style of MI tag
	public function regexTempIssues() {
		return '/\| ?'.$this->regexName().' ?= ?'.$this->dateregex.'(\r|\n){0,1}/i';
	}
	
	//returns the regex part for template name and redirects
	public function regexName() {
		$string = '('.$this->name."|".implode('|',$this->redirects).')';
		return preg_replace("/(\|\||\|\))/",")",$string);//remove any extra room in regex
	}
	
	//returns the regex part for arguments of a template
	private function regexArgs() {
		return '(\|([0-9a-zA-Z _]*?)( ?= ?[0-9a-zA-Z _]*?)){0,6}';
	}
	
	//returns regex part matching when not to add the tag
	public function regexNotif() {
		if(count($this->notif) == 0)
		{
			return false;
		}
		$string = "";//blank string
		foreach($this->notif as $nottemplate)
		{
			$string = $string.$nottemplate->regexName()."|";
		}
		$string = '('.$string.')';
		return preg_replace("/(\|\||\|\))/",")",$string);//remove any extra room in regex
	}

}
	 
?>