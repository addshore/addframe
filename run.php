<?

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

// perform the login
$wiki->login($config['user'],$config['password']);
unset($config['password']);

// connect to the database
$db = new Database( $config['dbhost'], $config['dbport'], $config['dbuser'], $config['dbpass'], $config['dbname'], false);

// get the current list of pending articles
$result = $db->select('pending','*');
$list = Database::mysql2array($result);
foreach ($list as $item)
{
	//$item['page'] is the name of the article in this case
}

?>