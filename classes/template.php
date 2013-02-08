<?

class Template {

	// construct the page (you probably want to call load after this)
	public function __construct($name,$redirects,$dated=false,$notif=null) {
		$this->name = $name;
		$this->redirects = $redirects;
		array_push($this->redirects,$this->name); //also push the name to the list to make it easier to use
		$this->dated = $dated;
		$this->notif = $notif;
		$this->rege = new regex;
	}	
	
	//Variables
	private $name;// template name e.g. Template:Orphan $page would be "Orphan"
	private $redirects;// stores redirects to here
	private $dated;//stores is the template tags a date arg
	private $notif;//dont use this if the templates in this array are on the page
	private $dateregex = $this->rege->dateregex();

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
		return '\{\{'.$this->regexName().$this->rege->templateargs(6,,,"sect(ions?)?").'\}\}(\r|\n){0,3}';
		//return '\{\{'.$this->regexName().$this->regexArgs().'\}\}(\r|\n){0,3}'; //changed to use regex.php
	}
	
	//matches in the old style of MI tag
	public function regexTempIssues() {
		return $this->rege->templatearg($this->regexName(),$this->dateregex()).'(\r|\n){0,1}'
		//return '\| ?'.$this->regexName().' ?= ?'.$this->dateregex.'(\r|\n){0,1}'; //changed to use regex.php
	}
	
	//returns the regex part for template name and redirects
	public function regexName() {
		return $this->rege->arraytoregex($this->redirects);
		//changed to use regex.php
		//$string = '('.$this->name."|".implode('|',$this->redirects).')';
		//return preg_replace("/(\|\||\|\))/",")",$string);//remove any extra room in regex
	}
	
	/* //changed to use regex.php
	//returns the regex part for arguments of a template
	private function regexArgs() {
		//return '(\|([0-9a-zA-Z _]*? ?= ?)?([0-9a-zA-Z _]*?)){0,6}';
		return '(\|(?!sect(ions?)?)([0-9a-zA-Z _]*? ?= ?)?([0-9a-zA-Z _]*?)){0,6}';//ignore templates with sections params
	}*/
	
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