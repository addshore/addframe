<?
//Include db stuff

//First of all see if we have room to run any database cleanups
$count = intval(exec("qstat |grep -c ' wd.del'"));
echo "There are $count delete queue processes running\n";
if($count <= 5)
{
	echo exec("echo 'php /data/project/addbot/wikidata/iwlinked_del.php' | qsub -N wd.del");
	
}

//exit();

//Require the list of sites we are running on
require '/data/project/addbot/wikidata/sites.php';
//we have loaded the sites
echo "Loaded ".count($run)." sites..\n";
$c = 0;
$jobs = intval(exec("qstat |grep -c 'wd.g.'"));
$o = $jobs;
//For each site we should be running on
foreach($run as $lang => $torun)
{
	//get the number of jobs in the gird currently
	$jobs = intval(exec("qstat |grep -c 'wd.g.'"));
	$o = $jobs;
	//Check to make sure our run tracker is not present
	//if(!file_exists ("/data/project/addbot/tmp/wikidataruntracker/run.$lang.tracker"))
	//{
		if($jobs < 400)
		{
			$jobs = intval(exec("qstat |grep -c 'wd.g.".substr($lang,0,4)."'"));
			if($jobs < $torun)
			{
				echo "\033[32m".exec("echo 'php /data/project/addbot/wikidata/g.php --lang=$lang' | qsub -N wd.g.$lang")."\033[0m\n";
				$c++;
			}else{echo "\033[33mAlready running too many jobs for $lang\033[0m\n";}
		}else{echo "\033[34mSkiping as too many jobs in the queue\033[0m\n";}
	//}else{echo "\033[33mSkiping $lang due to run tracker (It has run in the past 24 hours)\033[0m\n";}
}
echo "Total scripts run $c\n";
echo "Total scripts runing $o\n";
?>