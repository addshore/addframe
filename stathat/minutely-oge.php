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
doChecks();

function doChecks() 
{ 
global $db,$config;

//Wikidata Dispatch Stats
$text = get_data("http://www.wikidata.org/wiki/Special:DispatchStats");
preg_match_all('/'.preg_quote('<td align="right">Average</td><td align="right">-</td><td align="right">-</td><td align="right">','/').'([^<]+)<\/td>/',$text,$match);
stathat_ez_value($config['stathatkey'], "Wikidata Dispatch Pending" , intval(str_replace(',','',$match[1][0])));

//Total numer of articles left in the DB
$res1 = Database::mysql2array($db->doQuery("select count(*) as count from iwlinked;"));
$res2 = Database::mysql2array($db->doQuery("select count(*) as count from iwlink;"));
stathat_ez_value($config['stathatkey'], "Addbot - OLD DB count" , intval($res1[0]['count']));
stathat_ez_value($config['stathatkey'], "Addbot - NEW DB count" , intval($res2[0]['count']));
stathat_ez_value($config['stathatkey'], "Addbot - IW Removal - Remaining" , intval($res1[0]['count'] + $res2[0]['count']));

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