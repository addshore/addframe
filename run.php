<?
// Report all errors except E_NOTICE
error_reporting(E_ALL ^ E_NOTICE);

echo "loading...\n";
sleep(1);

// load the classes and stuff
require 'botclasses.php';
require 'database.php';
require 'page.php';
require 'config.php';

// initialise the wiki
$wiki = new wikipedia;
$wiki->url = "http://".$config['url']."/w/api.php";
global $wiki;

echo "Logging in...\n";
sleep(1);

// perform the login
$wiki->login($config['user'],$config['password']);
unset($config['password']);

echo "Connecting to database...\n";
sleep(1);

// connect to the database
$db = new Database( $config['dbhost'], $config['dbport'], $config['dbuser'], $config['dbpass'], $config['dbname'], false);

// get the current list of pending articles
$result = $db->select('pending','*');
$list = Database::mysql2array($result);

echo "Checking ".count($list)." articles\n";
sleep(1);
foreach ($list as $item)
{
	echo "Checking ".$item['article']."\n";
	$page = new Page($item['article'],$wiki);// create our page instance
	$page->parse();// parse the page
	
	//for reference (User|Wikipedia|FileMediawiki|Template|Help|Category|Portal|Book|Education( |_)program|TimedText)(( |_)talk)?)"
	// updated list = http://en.wikipedia.org/wiki/Wikipedia:Namespace
	switch($page->getNamespace()){
		case ""://article
			if ($page->isOrphan == false)//if the page is not an orphan
			{
				//remove orphan tag
				$page->removeTag($config['tag']['orphan']);
			}
			break;
		case "User talk":
			break;
		case "File":
			break;
	}
	
	//If page content is now different to the old page then POST
	
	sleep(999);//to be removed after testing
}

?>