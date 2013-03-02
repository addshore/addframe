<?php
$start_time = MICROTIME(TRUE);
//database
require '/data/project/addbot/classes/database.php';
require '/home/addshore/.password.db';
$db = new Database( 'i-000000b4.pmtpa.wmflabs', '3306', 'addshore', $config['dbpass'], 'addbot', false);
unset($config['dbpass']);
$res = Database::mysql2array($db->doQuery("select lang,count(*) from iwlinked group by lang;"));
//loginto wiki
require '/data/project/addbot/classes/botclasses.php';
require '/data/project/addbot/enwiki/config.php';
$wiki = new wikipedia;
$wiki->url = "http://meta.wikimedia.org/w/api.php";
$wiki->login($config['user'],$config['password']);
unset($config['password']);
echo "done\n";
//calculations
$stop_time = MICROTIME(TRUE);
$time = $stop_time - $start_time;
$out = "";
$c = 0;
foreach ($res as $r)
{
	$c = $c + intval($r['count(*)']);
}

//output to html page
$out .= "<html><head><title>IWLinked</title></head><body>\n";
$out .= "Please see below the current pages Addbot has to check\n";
$out .= "<div><table><tr><th>Site</th><th>Count</th></tr>\n";
foreach ($res as $r)
{
	$out .= "<tr><td>".$r['lang'].".wikipedia</td><td>".number_format($r['count(*)'],0,'.',',')."</td></tr>\n";
}
$out .= "<tr><td>Total</td><td>".number_format($c,0,'.',',')."</td></tr>\n";
$out .= "</table></div>";
$out .= "<small>Elapsed time was $time seconds.</small>";
$out .= "</body></html>";
file_put_contents("/data/project/public_html/addshore/iw.html",$out);

//reset
$out = "";

//output to meta page
$out .= "Please see below the current pages Addbot has to check\n";
$out .= "{| class='wikitable sortable plainlinks'\n|- style='white-space:nowrap;'\n! Site\n! Count\n";
foreach ($res as $r)
{
	$out .= "|-\n| ".$r['lang'].".wikipedia\n| ".number_format($r['count(*)'],0,'.',',')."\n";
}
$out .= "|-\n| '''Total'''\n| ".number_format($c,0,'.',',')."\n|}";
$out .= "<small>Elapsed time was $time seconds.</small>";
$wiki->edit("User:Addbot/Interwiki_Status",$out,"Interwiki Status",true);

?>