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

?>