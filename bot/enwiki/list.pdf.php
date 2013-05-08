<?
// load the classes and stuff
require '../../classes/botclasses.php';
require '../../classes/database.php';
require '../../classes/template.php';
require '../../config/database.cfg';
require '../../config/wiki.cfg';

// initialise the wiki
$wiki = new wikipedia;
$wiki->url = 'http://en.wikipedia.org/w/api.php';
global $wiki;

// perform the login
$wiki->login($config['user'],$config['password']);
unset($config['password']);
echo "done";

//Get the list
$list = file_get_contents('http://toolserver.org/~nikola/grep.php?pattern=.pdf&lang=en&wiki=wikipedia&ns=6');
$list = explode('<td><input type="reset"/></td>',$list);
$list = explode('<table border="1">',$list[1]);

preg_match_all('/\wiki\/(Image\:.*?)\"\>/i', $list[1], $images);
$images = $images[1];

echo "Connecting to DB...\n";
// connect to the database
$db = new Database( $config['dbhost'], $config['dbport'], $config['dbuser'], $config['dbpass'], $config['dbname'], false);

foreach ($images as $pdf)
{
    		usleep(1000);
			$res = $db->insert($config['tblist'],array('article' => $item,) ); // inset to database table
			if( !$res  ){echo $db->errorStr()."\n";} // if no result then break as we have an error ($db->errorStr())
			else{echo "Added ".$item." to database\n";}
}
//$wpi-&gt;forcepost('User:'.$user.'/Pdfbot/list',"Please add a list of pdf's here from [http://toolserver.org/~nikola/grep.php?pattern=%5C.pdf&lang=en&wiki=wikipedia&ns=6 here].",'Automatic list blanking (task complete).');

?>