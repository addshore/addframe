<?
//run.php --page="value"
$options = getopt("",Array("page::"));

echo "loading...";
sleep(1);

// load the classes and stuff
require 'classes/botclasses.php';
require 'classes/database.php';
require 'classes/page.php';
require 'classes/template.php';
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

////Wikidata Log
$page = new Page("User:Addbot/log/wikidata",$wiki);
if (strlen($page->getText()) < 5){echo "\n> Page less than 5 length (may not exist)";die();}
$page->setText(preg_replace("/===[^=]*?===\n(?<====\n)(?:(?!===).)*?done}}.*?(?=\s*===|$)/ims","",$page->getText()));
$page->fixWhitespace();
$wiki->edit($page->getName(),$page->getText(),"[[User:Addbot|Bot:]] Removing completed sections",true,true,null,false);

?>
