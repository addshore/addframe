<?php

//require
require __DIR__.'/../classes/stathat.php';
require __DIR__.'/../config/stathat.cfg';

//Here is the loop for the process
while (true)
{
	//We have to do this twice to account for difference in timming between the server and stathat
	//This is so that we do not get silly gaps in the graphs
	echo "Doing checks\n";
	doChecks();
	echo "Sleeping for 20..\n";
	sleep(20);
	echo "Doing checks\n";
	doChecks();
}

//Below is the function with the tasks to complete
function doChecks() 
{ 
	global $config;

	//Total number of jobs on LABS
	echo "3";
	stathat_ez_value($config['stathatkey'], "Tool Labs Jobs" , intval(exec("qstat -u '*' | grep -c @")));
} 

?>