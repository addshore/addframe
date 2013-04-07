<?
//Include db stuff
require '/data/project/addbot/classes/database.php';
require '/data/project/addbot/config/database.php'; 

//First of all see if we have room to run any database cleanups
$count = intval(exec("qstat |grep -c ' wd.del'"));
if($count < 10)
{
	exec("echo 'php /data/project/addbot/wikidata/iwlinked_del.php' | qsub -N wd.del");
}

//Then see if there is any room for db migration
$count = intval(exec("qstat |grep -c ' wd.dbm'"));
if($count < 1)
{
	exec("echo 'php /data/project/addbot/wikidata/dbmove.php' | qsub -N wd.dbm");
}

$count = intval(exec("qstat |grep -c ' wd.del'"));
if($count < 10)
{
	exec("echo 'php /data/project/addbot/wikidata/iwlinked_del.php' | qsub -N wd.del");
}

//Require the list of sites we are running on
require '/data/project/addbot/wikidata/sites.php';

//we have loaded the sites
echo "Loaded ".count($run)." sites..\n";

//For each site we should be running on
foreach($run as $lang => $todo)
{
	//get the number of jobs for this language in the grid currently
	$count = intval(exec("qstat |grep -c ' wd.g.".substr($lang,0,5)."'"));
	//if there is a possibility we can run more jobs
	if($count < $todo)
	{
		//check when the last job was submitted
		$qstat = exec("qstat |grep ' wd.g.".substr($lang,0,5)."'");
		//if the current date is NOT in the grid
		if(!strstr($qstat, date("m/d/Y")))
		{
			//lets get the number of records in the db for this language
			$res = Database::mysql2array($db->doQuery("select count(*) as count from iwlinked where lang='$lang';"));
			//if we have over 10 it might still be worth checking
			if($res[0]['count'] > 10)
			{
				//lets submit a new job!
				echo exec("echo 'php /data/project/addbot/wikidata/g.php --lang=$lang' | qsub -N wd.g.$lang")."\n";
				//Lets give everything time to catch up with what we have just done...
				echo "Sleeping for 30 seconds\n";
				sleep(30);
			}
		}
	}
}

?>