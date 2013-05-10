<?
//require
require __DIR__.'/../classes/stathat.php';
require __DIR__.'/../config/stathat.cfg';

//database
require __DIR__.'/../classes/database.php';
require __DIR__.'/../config/database.cfg'; 
$db = new Database( $config['dbhost'], $config['dbport'], $config['dbuser'], $config['dbpass'], $config['dbname'], false);
unset($config['dbpass']);

//Here is the loop for the process
while (true)
{
	doChecks();
	sleep(50)
}

//Below is the function with the tasks to complete
function doChecks() 
{ 
	global $db,$config;

	//Wikidata Dispatch Stats
	$text = get_data("http://www.wikidata.org/wiki/Special:DispatchStats");
	preg_match_all('/'.preg_quote('<td align="right">Average</td><td align="right">-</td><td align="right">-</td><td align="right">','/').'([^<]+)<\/td>/',$text,$match);
	stathat_ez_value($config['stathatkey'], "Wikidata Dispatch Pending" , intval(str_replace(',','',$match[1][0])));
	unset($text,$match);

	//Total number of articles left in the DB
	$res = $db->doQuery("select count(*) as count from iwlink;");
	if (!$res) {
		echo $db->errorStr();
	}
	$res1 = Database::mysql2array($res);
	stathat_ez_value($config['stathatkey'], "Addbot - IW Removal - Remaining" , intval($res1[0]['count']));
	unset($res,$res1,$res2);
} 

function get_data($url) {
	$ch = curl_init();
	$timeout = 5;
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
	curl_setopt($ch,CURLOPT_USERAGENT,'Addbot Wikimedia Bot');
	$data = curl_exec($ch);
	curl_close($ch);
	return $data;
}

?>