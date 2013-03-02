<?
//run.php --page="value"
$options = getopt("",Array("page::"));

echo "loading...";
sleep(1);

// load the classes and stuff
require '../classes/botclasses.php';
require '../classes/database.php';
require '../classes/page.php';
require '../classes/template.php';
require 'config.php';

//error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);
error_reporting(E_ERROR | E_WARNING | E_PARSE);

// initialise the wiki
$wiki = new wikipedia;
$wiki->url = "http://".$config['url']."/w/api.php";
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

$rmdone = Array("User:Addbot/log/wikidata","User:Addbot/log/wikidata/1","User:Addbot/log/wikidata/2","User:Addbot/log/wikidata/3","User:Addbot/log/wikidata/4","User:Addbot/log/wikidata/5","User:Addbot/log/wikidata/6","User:Addbot/log/wikidata/7");

////Wikidata Log
foreach($rmdone as $title)
{
	$page = new Page($title,$wiki);
	echo "\n$title";
	if (strlen($page->getText()) < 2){echo "\n> Page less than 2 length (may not exist)";continue;}
	$page->setText(preg_replace("/===[^=]*?===\n(?<====\n)(?:(?!===).)*?done}}.*?(?=\s*===|$)/ims","",$page->getText()));
	$page->fixWhitespace();
	$wiki->edit($page->getName(),$page->getText(),"[[User:Addbot|Bot:]] Removing 'done' sections",true,true,null,false);
}

?>
