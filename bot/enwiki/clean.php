<?
//run.php --page="value"
$options = getopt("",Array("page::"));

echo "loading...";
sleep(1);

// load the classes and stuff
require '../classes/botclasses.php';
require '/data/project/addbot/config/wiki.php';

//error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);
error_reporting(E_ERROR | E_WARNING | E_PARSE);

// initialise the wiki
$wiki = new wikipedia;
$wiki->url = "http://en.wikipedia.org/w/api.php";
global $wiki;

echo "\nLogging in...";
sleep(1);echo "..";

// perform the login
$wiki->login($config['user'],$config['password']);
unset($config['password']);
echo "done";

//Get further config stuff
$config = parse_ini_string(preg_replace("/(\<syntaxhighlight lang='ini'\>|\<\/syntaxhighlight\>)/i","",$wiki->getpage("User:Addbot/config")),true);
require 'config.php';//again
if($config['General']['run'] != true){echo "\nNot set to run"; die();}//if we are not meant to run die

$e = Array();
$e['name'] = Array();
$e['size'] = Array();
$e['text'] = Array();
$count = 8;
for ($i=0; $i<=7;$i++)
{
	$e['name'][$i] = "User:Addbot/log/wikidata/".$i;
	if($i == 0){$e['name'][$i] = "User:Addbot/log/wikidata";}
}

////Remove sections that are done
foreach($e['name'] as $key => $title)
{
	$e['text'][$key] = $wiki->getpage($title);
	echo "\n$title";
	if (strlen($e['text'][$key]) < 2){echo "\n> Page less than 2 length (may not exist)";continue;}
	$e['text'][$key] = preg_replace("/\n===[^=]*?===\n(?<====\n)(?:(?!===).)*?done}}.*?(?=\s*===|$)/ims","",$e['text'][$key]);
	$e['text'][$key] = preg_replace("/\n\n/","\n",$e['text'][$key]);
	$e['text'][$key] = preg_replace("/\n\n/","\n",$e['text'][$key]);
	$wiki->edit($title,$e['text'][$key],"[[User:Addbot|Bot:]] Removing 'done' sections",true,true,null,false);
	$e['size'][$key] = strlen($e['text'][$key]);
}

?>
