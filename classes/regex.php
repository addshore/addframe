<?

class Regex {

	public function __construct() {
	}	
	
	//Returns regex matching arguments of a template
	//$exclude = regex of arguments to exclude from the regex
	public function templateargs($exclude = null) {
		if($exclude == null)
		{
			return $this->templatearg().'{0,6}';
		}
		else
		{
			return '(\|(?!'.$exclude.')([0-9a-zA-Z _]*? ?= ?)?([0-9a-zA-Z _]*?)){0,6}';
		}
	}
	
	//returns regex matching dates that can be used in templates
	public function dateregex(){
	return '((January|February|March|April|May|June|July|August|September|October|November|December) ?20[0-9][0-9])';
	}
	
	//Returns regex matching a single argument of a template '|date = July 2012'
	//$par = parameter name (if not set can match anything)
	//$val = value for parameter (if not set can match anything)
	//$exc = regex to exclude from the parameter name if param name is not set
	public function templatearg($par = null, $val = null,$exc = null) {
		if($par != null && $val != null)
		{
			return '(\|('.$par.' ?= ?)?('$val'))';
		}
		elseif($val == null)
		{
			return '(\|('.$par.' ?= ?= ?)?([0-9a-zA-Z _]*?))';
		}
		elseif($par == null)
		{
			//if nothing is set to be excluded
			if($exc == null)
			{
				return '(\|([0-9a-zA-Z _]*? ?= ?)?('$val'))';
			}
			else
			{
				return '(\|(?!'.$exc.')([0-9a-zA-Z _]*? ?= ?)?('$val'))';
			}
		}
		
	}
	
	//Returns regex matching a selection of arguments of a template '|date = July 2012'
	//$count = the number of arguments to match
	//$par = Array of parameter name (if a value is not set can match anything)
	//$value = Array of value for parameter (if a value is not set can match anything)
	//$exc = regex to exclude from all parameter names if param name is not set
	public function templateargs($count,$par = null, $val = null, $exc = null) {
		$r = "(";
		//Loop through how many arguments we want
		for ($i = 1; $i <= $count; $i++)
		{
			$arg = $this->templatearg($par[$i],$val[$i],$exc)
			$r .= $arg."|";
		}
		$r = rtrim($r, "|")
		$r .= ")";
	}
	
	//Returns a regex for if the given regex were in nowiki tags
	//$regex = regex to look for inside of nowiki tags
	//$text = text to look in (if not set just return the regex that is needed for check against)
	public function innowiki($regex,$text = null) {
	}
	
	//Returns a regex for if the given regex were in html comment tags
	//$regex = regex to look for inside of html comment tags
	//$text = text to look in (if not set just return the regex that is needed for check against)
	public function incomment($regex,$text = null) {
	}
	
	//Converts an array into a regex matching every element of the array
	//$array = array to convert
	public function arraytoregex($array) {
		$r = "(";
		foreach($array as $part)
		{
			$r .= $this->stringtoregex($part).'|';
		}
		$r = rtrim($r, "|")
		$r .= ")"
		return $r;
		
	}
	
	//Converts an array into a regex matching every element of the array
	//$array = array to convert
	public function stringtoregex($string) {
		return preg_quote($string,'/');
	}
	
}
	 
?>