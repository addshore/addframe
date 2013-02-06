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
	
	//Returns regex matching a single argument of a template '|date = July 2012'
	//$arg = 
	//$value = 
	public function templatearg($arg = null, $value = null) {
		if($arg != null && $value != null)
		{
			return '(\|('.$arg.' ?= ?)?('$value'))';
		}
		elseif($value == null)
		{
			return '(\|('.$arg.' ?= ?= ?)?([0-9a-zA-Z _]*?))';
		}
		elseif($arg == null)
		{
			return '(\|([0-9a-zA-Z _]*? ?= ?)?('$value'))';
		}
		
	}
	
	//Returns a regex for if the given regex were in nowiki tags
	//$regex
	public function innowiki($regex) {
	}
	
	//Returns a regex for if the given regex were in html comment tags
	//$regex
	public function incomment($regex) {
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