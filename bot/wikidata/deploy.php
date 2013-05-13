<?php

// If we could run this on the grid and submit from there then we would
// But we will just have to make do with checking that this is still running every so often
// For now we will run this on tools-dev on a cron

$TORUN = 5; // This is the maximum number of jobs we want to spawn for this task at any one time
$RUNNING = ARRAY(); // This will be an array of the languages that are running 
$FILE = $file = __DIR__."/sites.php"; // Location for the list of languages
$LIST = Array(); // This will be the list of langs from the file
$EOF = ""; // This will be the last language that is in the file

//Load the list from the file and set the $EOF
$LIST = explode("\n",file_get_contents($file));
$EOF = $LIST[count($LIST)-1];

//We should try and do this forever
while(true)
{

	//For each language
	foreach ($LIST as $lang)
	{
	
		//If we are still running this job from the last cycle
		if(!isset($RUNNING[$lang]))
		{
		
			//While we are yet to queue this item
			$queued = false;
			while($queued == false)
			{
				//check the status of everything we have run already
				foreach($RUNNING as $oldlang => $ran)
				{
					//try to get each jobid
					$jid = exec("job wd-mig-$oldlang");
					//if we got nothing then it must be done
					if($jid == "")
					{
						//remove it from out running array
						$RUNNING[$oldlang] = false;
						unset($RUNNING[$oldlang]);
						echo "$oldlang is no longer running\n";
					}
				}
				
				//Do we have enough space to run?
				if(count($RUNNING) < $TORUN)
				{
					//ADD THE JOB TO THE GRID wd-mig-$lang
					exec("jsub -mem 1G -once -N wd-mig-$lang php /data/project/addbot/bot/wikidata/g.php");
					$RUNNING[$lang] = true;
					echo "Started $lang on the grid\n";
					$queued = true;
				}
				//If we do not then sleep until we can try again
				else{sleep(120);}
			}
			
		}
		
		//If we get to here then we have queued the last job
		//Now we need to see if we need to reload the list (only if we have just submitted the last lang
		if($EOF == $lang)
		{
			//Load the list from the file and set the new $EOF
			echo "Got to the end of the list, starting again\n";
			$LIST = explode("\n",file_get_contents($file));
			$EOF = $LIST[count($LIST)-1];
			break 2; //make sure we now start again
		}
	}
}
	
?>
