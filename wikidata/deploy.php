<?
//Include db stuff
require '/data/project/addbot/classes/database.php';
require '/data/project/addbot/config/database.php'; 

//First of all see if we have room to run any database cleanups
$count = intval(exec("qstat |grep -c ' wd.del'"));
echo "There are $count delete queue processes running\n";
if($count <= 10)
{
	exec("echo 'php /data/project/addbot/wikidata/iwlinked_del.php' | qsub -N wd.del");
}

//Then see if there is any room for db migration
$count = intval(exec("qstat |grep -c ' wd.dbm'"));
echo "There are $count db migration processes running\n";
if($count < 1)
{
	exec("echo 'php /data/project/addbot/wikidata/dbmove.php' | qsub -N wd.dbm");
}

//Require the list of sites we are running on
require '/data/project/addbot/wikidata/sites.php';

//we have loaded the sites
echo "Loaded ".count($run)." sites..\n";

//For each site we should be running on
foreach($run as $lang => $todo)
{
	//select the job name
	$job = "wd.g.$lang";
	if($lang == "simple") { $job = "wd.g.simpl";}
	//get the number of jobs for this language in the grid currently
	$count = intval(exec("qstat |grep -c ' wd.g.$job'"));
	//if there is a possibility we can run more jobs
	if($count < $todo)
	{
		//check when the last job was submitted
		$qstat = exec("qstat |grep ' wd.g.$job'");
		//if the current date is NOT in the grid
		if(!strstr($qstat, date("m/d/Y")))
		{
			//lets get the number of records in the db for this language
			$res = Database::mysql2array($db->doQuery("select count(*) as count from iwlinked where lang='$lang';"));
			//if we have 0 there is no point in running
			if($res[0]['count'] == 0)
			{
				//Finally check to make sure our run tracker is not present
				if(!file_exists ("/data/project/addbot/tmp/wikidataruntracker/run.$lang.tracker"))
				{
					//lets submit a new job!
					echo exec("echo 'php /data/project/addbot/wikidata/g.php --lang=$lang' | qsub -N wd.g.$lang")."\n";
					//Lets give everything time to catch up with what we have just done...
					echo "Sleeping for 30 seconds\n";
					sleep(30);
				}else{echo "Skiping $lang due to run tracker\n";}
			}else{echo "Skiping $lang due to no db rows\n";}
		}else{echo "Skiping $lang due running today\n";}
	}else{echo "Skiping $lang due running max instances\n";}
}

?>