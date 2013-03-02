<?PHP

require 'bot.login.php';
global $wiki;

$list = $wiki->categorymembers("Category:All articles to be merged",true);

$log = "";
$counter = 0;

foreach($list as $page)
echo"\n\n\n\n";
{if($counter < 10){
	$text = $wiki->getpage($page);
	
	//match the template
	preg_match('/\{\{(Multi(ple)?mergefrom|Mergrefrom|(Proposed )|Merge(( |-)?(tomultiple-with|with|disputed|from(-?(section|multi(ple)?|category))?|section|split|multi(ple(-to)?)?))?|Multi(merge|plemergeinto)|Include)\|[a-zA-z0-9/()]\|( ?(date) ?(= ?(January|February|March|April|May|June|July|August|September|October|November|December) ?20[0-9][0-9])? ?){0,1} *\}\}(\r\n|\n\n){0,3}/i',$text,$match);
	print_r($match);
	sleep(999);
	//Detect where we want the redirect to go to
	$merge = $match[13];
	
	//If page is empty
	if($wiki->getpage($merge) == "")
	{
		//Remove the template
		$text = preg_replace('/\{\{(Multi(ple)?mergefrom|Mergrefrom|(Proposed )|Merge(( |-)?(tomultiple-with|with|disputed|from(-?(section|multi(ple)?|category))?|section|split|multi(ple(-to)?)?))?|Multi(merge|plemergeinto)|Include)\|([a-zA-z0-9 .\#*()_+=-\\]*)\|( ?(date) ?(= ?(January|February|March|April|May|June|July|August|September|October|November|December) ?20[0-9][0-9])? ?){0,1} *\}\}(\r\n|\n\n){0,3}/i',"",$text);
		
		//$wiki->edit($page,$text,"[[User:Addbot|Bot:]] Removing Merge tag as target page does not exist ([[User_talk:Addbot|Report Errors]])",true);
		$log = $log."[[$page]] >> [[$merge]]\n";
		$counter++;
	}
}}

$wiki->edit("User:Addshore/sandbox",$log,"[[User:Addbot|Bot:]] Posting query results)",true);

?>