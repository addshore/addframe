<?

class Regex {
	
	//returns regex matching dates that can be used in templates
	function date(){
		return '((January|February|March|April|May|June|July|August|September|October|November|December) ?20[0-9][0-9])';
	}
	//returns regex matching any wikilink
	function wikilink(){
		return '\[\[([^]:]*)\]\]';
	}
	
	//Returns regex matching a single argument of a template '|date = July 2012'
	//$par = parameter name (if not set can match anything)
	//$val = value for parameter (if not set can match anything)
	//$exc = regex to exclude from the parameter name if param name is not set
	function templatearg($par = null, $val = null,$exc = null) {
		if(!isset($par)){$par = "[0-9a-zA-Z _]*?";}
		else{}
		if(!isset($val)){$val = "[0-9a-zA-Z _]*?";}
		else{}
		if(!isset($exc)){$exc = "";}
		else{$exc = "(?!".$exc.")"}
		return '(\|'.$exc.'('.$par.' ?= ?)?('.$val.'))';
		
	}
	
	//Returns regex matching a selection of arguments of a template '|date = July 2012'
	//$count = the number of arguments to match
	//$par = Array of parameter name (if a value is not set can match anything)
	//$value = Array of value for parameter (if a value is not set can match anything)
	//$exc = regex to exclude from all parameter names if param name is not set
	function templateargs($count,$par = null, $val = null, $exc = null) {
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
	
	//Converts an array into a regex matching every element of the array
	//$array = array to convert
	function arraytoregex($array) {
		foreach($array as $part)
		{
			$r .= $this->stringtoregex($part).'|';
		}
		$r = trim($r, "|");
		$r = "(".$r.")";
		return $r;
		
	}
	
	//Converts an array into a regex matching every element of the array
	//$array = array to convert
	function stringtoregex($string) {
		return preg_quote($string,'/');
	}
	
}
	 
?>