<?
//Classes and configs
require __DIR__.'/../classes/botclasses.php';
require __DIR__.'/../config/wiki.cfg';

//and wikidata
$wiki      = new wikipedia;
$wiki->url = "http://commons.wikimedia.org/w/api.php";
global $wiki;
echo "\nLogging in to commons.wikimedia.org...";
$wiki->login("", "");

$reason = "Standardize file names";
$filemovepage = "User:CommonsDelinker/commands/filemovers";
$todo = $wiki->getpage($filemovepage)."\n";

$pages = $wiki->categorymembers("Category:SVG association football flags");
echo count($pages)."\n";
foreach($pages as $page)
{
	$new = str_replace('600px ','',$page);
	if($page != $new)
	{
		echo "$page -> $new\n";
		//function move ($old,$new,$reason,$options=null) {
		$pages = $wiki->move($page,$new,$reason,"movetalk|movesubpages");
		$template = "{{universal replace|".preg_replace('/^File:/','',$page)."|".preg_replace('/^File:/','',$new)."|reason=[[COM:FR|File renamed]]: $reason}}\n";
		echo $template;
		$todo .= $template;
		sleep(10);
	}
}

//Now add the todo to a page
$wiki->edit($filemovepage,$todo,"Requesting after moves",true,true,null,true,"0");
echo "Posted";

?>
