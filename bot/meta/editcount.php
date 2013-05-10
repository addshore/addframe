<?
//run.php --page="value"
$options = getopt("",Array("page::"));

echo "loading...";
sleep(1);

// load the classes and stuff
require __DIR__.'../../classes/botclasses.php';
require __DIR__.'../../config/wiki.cfg';

//error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);
error_reporting(E_ERROR | E_WARNING | E_PARSE);

// initialise the wiki
$wiki = new wikipedia;
$wiki->url = "http://meta.wikimedia.org/w/api.php";
global $wiki;

echo "Logging in...\n";
sleep(1);echo "..";

// perform the login
$wiki->login($config['user'],$config['password']);
unset($config['password']);
echo "done\n";

$text = $wiki->getpage("User:Addbot");
preg_match_all('/\{\{User contrib\|\d{1,10}\|project=wikipedia\|lang=([^\}]+)\}\}/',$text,$matches);
foreach($matches[0] as $key => $m)
{
	$w = new wikipedia;
	$w->url = "http://".$matches[1][$key].".wikipedia.org/w/api.php";
	$count = $w->contribcount("Addbot");
	echo $matches[1][$key]." has $count \n";
	if($count > 0){
		$text = str_replace($m,"{{User contrib|$count|project=wikipedia|lang=".$matches[1][$key]."}}",$text);
	}
	unset($w);
}
echo "posting\n";
$wiki->edit("User:Addbot",$text,"Updating edit counts",true);
?>
