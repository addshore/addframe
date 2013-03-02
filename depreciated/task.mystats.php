<?PHP

require 'bot.login.php';
global $wiki;

$page = "User:Addbot/Stats";

//Get the last run date
$text = $wiki->getpage($page);
preg_match("/\<!\-\- (.*) \-\-\>/i",$text,$timestamp);
//$end = $timestamp[1];
$end = "2008-01-01T00:00:00Z";
$start = '';
$done = false;

$sum['AWB']['regex'] = "/((clean|fix) and tag|tagging and fixing|Maintenance tags)/i";
$sum['sandbox']['regex'] = "/Restoring/i";
$sum['message']['regex'] = "/Sending/i";
$sum['badformat']['regex'] = "/Tagging PDF with \{\{BadFormat\}\}/i";
$sum['usertalksubst']['regex'] = "/Substing/i";
$sum['doubleredirects']['regex'] = "/Fixing double redirect/i";
$sum['referencessection']['regex'] = "/Adding missing (\<references \/\>|\{\{reflist\}\}) tag/i";
$sum['deorphan']['regex'] = "/(Removing orphan tag|No ?longer an Orphan|Successfully de\-orphaned)/i";
$sum['orphan']['regex'] = "/(Adding Orphan Tag|Orphan page, add template)/i";
$sum['deuncat']['regex'] = "/Removing Uncategorized (template|tag)/i";
$sum['uncat']['regex'] = "/Adding uncategorized tag/i";
$sum['deadend']['regex'] = "/Removing Deadend Tag/i";
$sum['sections']['regex'] = "/Removing sections tag/i";
$sum['unknown']['regex'] = "//";
foreach ($sum as $type => $check)
{
	$sum[$type]['count'] = 0;
}

while($done == false)
{
	//Run the query
	sleep(60);
	$x = $wiki->query('?action=query&format=php&list=usercontribs&ucuser=Addbot&uclimit=5000&ucstart='.urlencode($start).'&ucend='.urlencode($end).'&ucprop=comment|timestamp|title');
	
	//If we are given a place to continue
	if(isset($x['query-continue']['usercontribs']['ucstart']))
	{
		//Get the query continue timestamp
		$start = $x['query-continue']['usercontribs']['ucstart'];
		$done = false;
	}
	else
	{
		//Looks like we are done for now, Need to get the timestamp for the next run
		$done = true;
		$start = $x['query']['usercontribs'][0]['timestamp'];
	}
	
	//Look at each contrib
	foreach ($x['query']['usercontribs'] as $usercontrib)
	{
		//Check the summary against regex
		foreach ($sum as $type => $check)
		{
			//Search for a match
			if(preg_match($check['regex'],$usercontrib['comment']))
			{
				echo $usercontrib['comment']."\n";
				$sum[$type]['count']++;
				break;
			}
		}
	}	
}

/*
//Now we need to parse the previous results
preg_match_all('/\|\-\n! Task !! Count\n\|\-\n| (.*)\n\| ([0-9]+)/i',$text,$matches);
foreach($matches[1] as $id => $cont)
{
	if($id != 0)
	{
		//Add the values to those that we have calculated
		$sum[$cont]['count'] = $sum[$cont]['count'] + $matches[2][$id];
	}
}
*/
	
//Create the table
$output = "<!-- $start -->\n";
$output =$output.'{| class="wikitable"
|-
! colspan="2" | Addbot statistics
|-
! Task !! Count';

//Add each value
foreach ($sum as $type => $check)
{
	$output = $output."\n|-\n| ".$type."\n| ".$sum[$type]['count'];
}

$output = $output."\n|}";

echo $output;

$wiki->edit($page,$output,"[[User:Addbot|Bot:]] Posting Stats",true);

?>