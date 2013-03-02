<?PHP
error_reporting(E_ALL);
ini_set('display_errors', '1');

require 'bot.login.php';
global $wiki;

$arg = $argv[1];

//split the list
$articles = $wiki->categorymembers($arg,true);
//$articles = $wiki->whatlinkshere($arg);

foreach($articles as $article)
{
	sleep(10);
	//$pid = pcntl_fork();
	//if(!$pid){
		exec("python /home/addshore/addbot/pywikipedia/lonelypages.py -always -page:".preg_replace("/ /","_",$article));
	//	break;
	//}
}
?>