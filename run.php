<?

echo "loading...\n";
sleep(1);

// load the classes
require 'botclasses.php';
require 'database.php';
require 'page.php';

// load the configs
require 'config.gen.php';
require 'config.regex.php';

// initialise the wiki
$wiki = new wikipedia;
$wiki->url = 'http://'.$config['url'].'/w/api.php';
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
	$page = new Page($item['article']);// create our page instance
	$page->parse();// parse the page
}

?>