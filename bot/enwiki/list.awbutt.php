<?PHP
// load the classes and stuff
require '../../classes/botclasses.php';
require '../../classes/database.php';
require '../../classes/template.php';
require '../../config/database.php';
require '../../config/wiki.php';

// initialise the wiki
$wiki = new wikipedia;
$wiki->url = 'http://en.wikipedia.org/w/api.php';
global $wiki;

// perform the login
$wiki->login($config['user'],$config['password']);
unset($config['password']);
echo "done";

//Get the list of templates and strip away the rubbish
$awbutt = explode('expand the template(s) on the user talk page.',$wiki->getpage('Wikipedia:AutoWikiBrowser/User_talk_templates'));
$awbutt = str_ireplace(']]','',str_ireplace('# [[','',str_ireplace(']]# [[','|',preg_replace ("/\n/",'',$awbutt[1]))));
$awbutt = explode('|',$awbutt);
echo "Got the list\n";

echo "Connecting to DB...\n";
// connect to the database
$db = new Database( $config['dbhost'], $config['dbport'], $config['dbuser'], $config['dbpass'], $config['dbname'], false);

//check each template
foreach ($awbutt as $template)
{
	sleep(1);
	echo "Geting Transclusions for $template\n";
	//Get the pages the template is found on
	$pages = $wiki->getTransclusions($template,null,"&einamespace=3");
	
	if(count($pages) > 0 )
	{
		foreach($pages as $item) // for each item
		{
			usleep(1000);
			$res = $db->insert($config['tblist'],array('article' => $item,) ); // inset to database table
			if( !$res  ){echo $db->errorStr()."\n";} // if no result then break as we have an error ($db->errorStr())
			else{echo "Added ".$item." to database\n";}
		}
	}
}

?>
