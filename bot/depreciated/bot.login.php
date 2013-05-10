<?

error_reporting(E_ALL);
ini_set('display_errors', 1);

require __DIR__.'/../../classes/botclasses.php';

$wiki = new wikipedia;
$wiki->url = 'http://en.wikipedia.org/w/api.php';
global $wiki;

$parentpid = posix_getpid();

$user = "Addbot";
$nickname = "Addbot";
$owner = "Addshore";

$mysandbox = "User:".$owner."/Sandbox";

set_time_limit(0); 
require __DIR__.'/../../config/wiki.cfg';
$wiki->login($config['user'],$config['password']);
echo "USER: Logged In!\n";
unset($config['password']);

?>
