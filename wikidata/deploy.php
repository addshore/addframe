<?
//Include db stuff

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
if($count < 4)
{
	exec("echo 'php /data/project/addbot/wikidata/dbmove.php' | qsub -N wd.dbm");
}

//Require the list of sites we are running on
require '/data/project/addbot/wikidata/sites.php';

//we have loaded the sites
echo "Loaded ".count($run)." sites..\n";

$c = 0;
//For each site we should be running on
foreach($run as $lang => $todo)
{
	//get the number of jobs in the gird currently
	$jobs = intval(exec("qstat |grep -c 'wd.g.'"));
	//Check to make sure our run tracker is not present
	if(!file_exists ("/data/project/addbot/tmp/wikidataruntracker/run.$lang.tracker"))
	{
		if($jobs < 400)
		{
		//find out how many instances of the script we want to run depending on db size (trying to finish in 24 hours)
		$torun = 0;
		for($i = 30; $i != -1; $i--)
		{
			if($todo < $i*50000) { $torun = $i; }
		}
		for($i = 0; $i < $torun; $i++)
		{
			$offset = $i*50000;
			echo "$lang - $offset\n";
			$c++;
			echo "\033[33m".exec("echo 'php /data/project/addbot/wikidata/g.php --lang=$lang --offset=$offset' | qsub -N wd.g.$lang")."\033[0m\n";
		}
		}else{echo "\033[33mSkiping as too many jobs in the queue\033[0m\n";}
	}else{echo "\033[33mSkiping $lang due to run tracker (It has run in the past 24 hours)\033[0m\n";}
}
echo "Total scripts run $c\n";
?>