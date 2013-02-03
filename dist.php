<?
/*
This script is made to manage which server a new instance of the bot is spawned on
depending upon the loads of the servers defined below.
*/

//script options
$shortopts = "l";
$options = getopt($shortopts);

//if we were set to get the load do so
if(isset($options['l']))
{
	$load = sys_getloadavg();
	echo $load[2];
	die();
}

//if we are still running it is because we are the master
$insts = Array('bots-4','bots-3');//define all instances the bot can run on
$toinst = "";//the instance we want to run on
$toload = 999;//lowest load found so far

//Check the load of each server
foreach($insts as $inst)
{
	//get load
	$result = exec('ssh addshore@'.$inst.' /data/project/addbot/dist.php -l');
	$result = intval($result);
	//if new load is lower than old load
	if($result < $toload)
	{
		//update which instance we want to use
		$toload = $result;
		$toinst = $inst;
	}
}

//run on our selected instance
$result = exec('ssh addshore@'.$toinst.' /data/project/addbot/run.php');

?>
