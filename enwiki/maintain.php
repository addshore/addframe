<?
require 'classes/botclasses.php';
require 'classes/database.php';
require 'config.php';

//echo "Connecting to DB...\n";
//$db = new Database( $config['dbhost'], $config['dbport'], $config['dbuser'], $config['dbpass'], $config['dbname'], false);

// initialise the wiki
$wiki = new wikipedia;
$wiki->url = "http://".$config['url']."/w/api.php";
global $wiki;
$wiki->login($config['user'],$config['password']);
unset($config['password']);

//get the config
$text = $wiki->getpage("User:Addbot/config");
eval(preg_replace("/(\<syntaxhighlight lang='php'\>|\<\/syntaxhighlight\>)/i","",$text));//run the config

foreach($config['tag'] as $tag)//foreach tag defined
{
	$redirects = $wiki->whatlinkshere("Template:".$tag->getName(),"&blfilterredir=redirects&blnamespace=10");//get the template redirects
	$text = $text."\n".'$config'."['tag']['".$tag->getName()."'] = new Template('".$tag->getName()."',Array('".implode("','",$redirects)."'),array('date'));";
}

$wiki->edit("User:Addbot/config",$text,"Update Config",true);


?>