<?
//require
require '/data/project/addbot/classes/stathat.php';
require '/data/project/addbot/config/stathat.php';

//database
require '/data/project/addbot/classes/database.php';
require '/data/project/addbot/config/database.php'; 
$db = new Database( $config['dbhost'], $config['dbport'], $config['dbuser'], $config['dbpass'], $config['dbname'], false);
unset($config['dbpass']);

doChecks();
sleep(20);
//doChecks();

function doChecks() 
{ 
global $db,$config;

//Wikidata Dispatch Stats
$text = get_data("http://www.wikidata.org/wiki/Special:DispatchStats");
preg_match_all('/'.preg_quote('<td align="right">Average</td><td align="right">-</td><td align="right">-</td><td align="right">','/').'([^<]+)<\/td>/',$text,$match);
stathat_ez_value($config['stathatkey'], "Wikidata Dispatch Pending" , intval(str_replace(',','',$match[1][0])));
unset($text,$match);

//decide how loaded the mysql server is
$res = Database::mysql2array($db->doQuery("SHOW PROCESSLIST;"));
$c = 0;
foreach($res as $p)
{
	if($p['db'] == 'addbot')
	{
		$c++;
	}
}
stathat_ez_value($config['stathatkey'], "Addbot - IW Removal - Queued Requests" , $c);
unset($res,$c);

//Total number of articles left in the DB
$res1 = Database::mysql2array($db->doQuery("select count(*) as count from iwlink;"));
stathat_ez_value($config['stathatkey'], "Addbot - IW Removal - Remaining" , intval($res1[0]['count']));
unset($res1,$res2);
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