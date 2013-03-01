<?

class Template {

	// construct the page (you probably want to call load after this)
	public function __construct($name,$redirects,$dated=false,$notif=null) {
		$this->name = $name;
		$this->redirects = $redirects;
		array_push($this->redirects,$this->name); //also push the name to the list to make it easier to use
		$this->dated = $dated;
		$this->notif = $notif;
	}	
	
	//Variables
	private $name;// template name e.g. Template:Orphan $page would be "Orphan"
	private $redirects;// stores redirects to here
	private $dated;//stores is the template tags a date arg
	private $notif;//dont use this if the templates in this array are on the page
	private $rege;

	//returns regex matching dates that can be used in templates
	private function date(){
		return '((January|February|March|April|May|June|July|August|September|October|November|December) ?20[0-9][0-9])';
	}
	
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
	
	//Returns the regex for matching the whole template and up to 6 arguments (not including 'sections'
	public function regexTemplate() {
		return '\{\{'.$this->regexName().$this->templateargs(6,null,null,"sect(ions?)?").'\}\}(\r|\n){0,3}';
	}
	
	//Matches the template as an argument (used in the old MI style
	public function regexTempIssues() {
		return $this->templatearg($this->regexName(),$this->date()).'(\r|\n){0,1}';
	}
	
	//returns the regex part for template name and redirects
	public function regexName() {
		return $this->arraytoregex($this->redirects);
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
	
	//Converts an array into a regex matching every element of the array
	//$array = array to convert
	private function arraytoregex($array) {
		foreach($array as $part)
		{
			$r .= preg_quote($part,'/').' *?|';
		}
		$r = trim($r, "|");
		$r = "(".$r.")";
		return $r;
		
	}
	
	//Returns regex matching a single argument of a template '|date = July 2012'
	//$par = parameter name (if not set can match anything)
	//$val = value for parameter (if not set can match anything)
	//$exc = regex to exclude from the parameter name if param name is not set
	private function templatearg($par = null, $val = null,$exc = null) {
		if(!isset($par)){$par = "[0-9a-zA-Z _]*?";}
		else{}
		if(!isset($val)){$val = "[0-9a-zA-Z _]*?";}
		else{}
		if(!isset($exc)){$exc = "";}
		else{$exc = "(?!".$exc.")";}
		return '(\|'.$exc.'('.$par.' ?= ?)?('.$val.'))';
		
	}
	
	//Returns regex matching a selection of arguments of a template '|date = July 2012'
	//$count = the number of arguments to match
	//$par = Array of parameter name (if a value is not set can match anything)
	//$value = Array of value for parameter (if a value is not set can match anything)
	//$exc = regex to exclude from all parameter names if param name is not set
	private function templateargs($count,$par = null, $val = null, $exc = null) {
		$r = "(";
		//Loop through how many arguments we want
		for ($i = 1; $i <= $count; $i++)
		{
			$arg = $this->templatearg($par[$i],$val[$i],$exc);
			$r .= $arg."|";
		}
		$r = rtrim($r, "|");
		$r .= ")";
		return $r;
	}
	
}
	 
?>