<?
//run.php --page="value"
$options = getopt("",Array("page::"));

echo "loading...";
sleep(1);

// load the classes and stuff
require '/data/project/addbot/classes/botclasses.php';
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

foreach($e['size'] as $key1 => $s1)
{
	foreach($e['size'] as $key2 => $s2)
	{
		//if we are on the main log page done check;
		if($key1 == 0 || $key2 == 0){continue;}
		if($key1 == $key2){continue;}
		//if we can merge
		if($s1+$s2 <= 180000)
		{
			//merge
			$e['text'][$key1] = $e['text'][$key1]."\n".str_replace("__NOTOC__","",$e['text'][$key2]);
			$e['text'][$key2] = "__NOTOC__\n";
			$e['size'][$key1] = strlen($e['text'][$key1]);
			$e['size'][$key2] = strlen($e['text'][$key2]);
			$wiki->edit($e['name'][$key1],$e['text'][$key1],"[[User:Addbot|Bot:]] Merging page $key2 to $key1",true,true,null,false);
			$wiki->edit($e['name'][$key2],$e['text'][$key2],"[[User:Addbot|Bot:]] Merged page $key1 to $key2",true,true,null,false);
			echo "\nMerging $key2 to $key1";
		}
	}
}

foreach($e['size'] as $key => $s)
{
	//if not the main log
	if($key == 0){continue;}
	if($e['size'][0] > 150000)
	{
		if ($s <= 15)
		{
			$e['text'][0] = $wiki->getpage($e['name'][0]);
			$e['text'][$key] = str_replace("\n'''See [[/1]], [[/2]], [[/3]], [[/4]], [[/5]], [[/6]], [[/7]], [[/8]] for lists that are yet to be checked!'''","",$e['text'][0]);
			$e['text'][0] = "__NOTOC__\n'''See [[/1]], [[/2]], [[/3]], [[/4]], [[/5]], [[/6]], [[/7]], [[/8]] for lists that are yet to be checked!'''\n";
			$wiki->edit($e['name'][0],$e['text'][0],"[[User:Addbot|Bot:]] Moved to page $key",true,true,null,false);
			$wiki->edit($e['name'][$key],$e['text'][$key],"[[User:Addbot|Bot:]] Moved from main page",true,true,null,false);
			echo "\nMoving 0 to $key";
			break;
		}
	}
}

?>
