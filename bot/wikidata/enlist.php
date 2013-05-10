<?PHP
// load the classes and stuff
require __DIR__.'../../classes/botclasses.php';
require __DIR__.'../../classes/database.php';
require __DIR__.'../../config/database.cfg';
require __DIR__.'../../config/wiki.cfg';

// initialise the wiki
$wiki = new wikipedia;
$wiki->url = 'http://en.wikipedia.org/w/api.php';
global $wiki;

// perform the login
$wiki->login($config['user'],$config['password']);
unset($config['password']);
echo "done";

//Get further config stuff
$config = parse_ini_string(preg_replace("/(\<syntaxhighlight lang='ini'\>|\<\/syntaxhighlight\>)/i","",$wiki->getpage("User:Addbot/config")),true);
require __DIR__.'../enwiki/config.cfg';//again
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
	//Increment the on wiki counter
	$at = $at+$am;
	$c = 0;

	//start addition to db
	$rs = "INSERT INTO iwlinked (lang, article) VALUES ";
	$r = "";
	foreach($list as $item) // for each item
	{
		if( $item != "")
		{
			$c++;
			$r .= "('en','".$db->mysqlEscape($item)."'),";
		}
	}
	echo "Inserting $c pages\n";
	$r = preg_replace('/,$/','',$r);//remove final ,
	$res = $db->doQuery($rs.$r);
	if( !$res  ){echo $db->errorStr();die("DB ERROR");}
	$wiki->edit("User:Addbot/iwval.js",$at,"[[User:Addbot|Bot:]] Updating counter",true);
}

?>
