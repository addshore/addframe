<?PHP
// load the classes and stuff
require 'classes/botclasses.php';
require 'classes/database.php';
require 'classes/template.php';
require 'config.php';

// initialise the wiki
$wiki = new wikipedia;
$wiki->url = 'http://'.$config['url'].'/w/api.php';
global $wiki;

// perform the login
$wiki->login($config['user'],$config['password']);
unset($config['password']);
echo "done";

//Get further config stuff
$config = parse_ini_string(preg_replace("/(\<syntaxhighlight lang='ini'\>|\<\/syntaxhighlight\>)/i","",$wiki->getpage("User:Addbot/config")),true);
require 'config.php';//again
if($config['General']['run'] != true){echo "\nNot set to run"; die();}//if we are not meant to run die

$at = $wiki->getpage('User:Addbot/iwval.js');
if($at == ""){die();}
$am = $config['IW']['tstoget'];

$page = file_get_contents("http://toolserver.org/~tb/langlinks/?from=$at&howmany=$am");
if($page == ""){die();}
$sect = explode("<HR>",$page);
$sect = explode("<DIV",$sect[3]);
$list = explode("<BR>",trim($sect[0]));
echo "Got the list\n";

echo "Connecting to DB...\n";
// connect to the database
$db = new Database( $config['dbhost'], $config['dbport'], $config['dbuser'], $config['dbpass'], $config['dbname'], false);

//If more than 1 returned
if(count($list) > 0 )
{
	$at = $at+$am;
	$wiki->edit("User:Addbot/iwval.js",$at,"[[User:Addbot|Bot:]] Updating counter",true);
	foreach($list as $item) // for each item
	{
		if( $item != "")
		{
			usleep(1000);
			$res = $db->insert($config['tblist'],array('article' => $item,) ); // inset to database table
			if( !$res  ){echo $db->errorStr()."\n";} // if no result then break as we have an error ($db->errorStr())
			else{echo "Added ".$item." to database\n";}
		}
	}
}

?>
