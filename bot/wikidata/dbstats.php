<?php
$start_time = MICROTIME(TRUE);

//database
require __DIR__.'../../classes/database.php';
require __DIR__.'../../config/database.cfg'; 
$db = new Database( $config['dbhost'], $config['dbport'], $config['dbuser'], $config['dbpass'], $config['dbname'], false);
unset($config['dbpass']);
$res = Database::mysql2array($db->doQuery("select lang,count(*) from iwlink group by lang;"));
//loginto wiki
require __DIR__.'../../classes/botclasses.php';
require __DIR__.'../../config/wiki.cfg';
$wiki = new wikipedia;
$wiki->url = "http://meta.wikimedia.org/w/api.php";
$wiki->login($config['user'],$config['password']);
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

//output wiki
$out = "";
$out .= "Please see below the current pages [[User:Addbot|Addbot]] has to check on its current run\n\nIf a site does not appear this doesn't mean there are no pages with IW links or we are waiting for the next run!\n\n";
$out .= "{| class='wikitable sortable plainlinks'\n|- style='white-space:nowrap;'\n! Site\n! Count\n";
foreach ($res as $r)
{
	$out .= "|-\n| ".$r['lang'].".wikipedia\n| ".number_format($r['count(*)'],0,'.',',')."\n";
}
$out .= "|-\n| '''Total'''\n| ".number_format($c,0,'.',',')."\n|}";

//Login and post both
$wiki = new wikipedia;
$wiki->url = "http://meta.wikimedia.org/w/api.php";
$wiki->login($config['user'],$config['password']);
$wiki->edit("User:Addbot/Interwiki_Status",$out,"Interwiki Status $c",true);
unset($wiki);
$wiki = new wikipedia;
$wiki->url = "http://www.wikidata.org/w/api.php";
$wiki->login($config['user'],$config['password']);
$wiki->edit("Wikidata:Wikidata_migration/Sitelink_removal/Progress",$out,"Interwiki Status $c",true);

//now determin how may runs we should do on higher end wikis!
$file = "sites.php";
$text = "";
//for each result in the db
foreach ($res as $r)
{
	$lang = str_replace('_','-',$r['lang']);
	$count = $r['count(*)'];
	//determine what get should be
	if($count < 1){$get = 0;}
	if($count > 0){$get = 1;}
	
	$text .= "$lang\n";
}
//put the file back
file_put_contents($file,$text);

?>
