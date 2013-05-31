#! /usr/bin/php5

<?php

// If we could run this on the grid and submit from there then we would
// But we will just have to make do with checking that this is still running every so often
// For now we will run this on tools-dev on a cron daily

//Database
require __DIR__.'/../classes/database.php';
require __DIR__.'/../config/database.cfg'; 

//Connect to the DB
$db = new Database( $config['dbhost'], $config['dbport'], $config['dbuser'], $config['dbpass'], $config['dbname'], false);
unset($config['dbpass']);
$LIST = Database::mysql2array($db->doQuery("select lang,count(*) from iwlink group by lang;"));

//For each language
foreach ($LIST as $row)
{
	//try to submit the job to the grid
	//Remember this will only allow one job with this name on the grid at any one time
	$lang = $row['lang'];
	echo shell_exec("/usr/local/bin/jsub -mem 700M -once -N wd-mig-$lang php /data/project/addbot/bot/wikidata/g.php --lang=$lang");
	//Then sleep for an apropriate ammount of time
	//This should deploy this script over the period of an hour
	sleep(86400/count($LIST));
}

//Then end as this script will be run again by cron

?>
