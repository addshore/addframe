<?PHP

require 'bot.login.php';
require 'functions.parsetemplates.php';
global $wiki;

//Set the page variables
$page['main'] = "Wikipedia:Bots/Status";
$page['active'] = "Wikipedia:Bots/Status/active_bots";
$page['inactive'][0] = "Wikipedia:Bots/Status/inactive_bots";
$page['inactive'][1] = "Wikipedia:Bots/Status/inactive_bots_2";
$header = "<noinclude>Please keep this list alphabetical.\n{{BotS/Top}}</noinclude>\n";
$footer = "<noinclude>{{BotS/Bottom}}</noinclude>";

$today = getdate();

//Get the content
$text['active'] = $wiki->getpage($page['active']);
$text['inactive'] = "";
foreach($page['inactive'] as $x)
{
	$text['inactive'] = $text['inactive'].$wiki->getpage($x);
}

//Strip the headers and footers
foreach($text as $id => $x)
{
	$x = preg_replace("/\<noinclude\>Please keep this list alphabetical\.\n\{\{BotS\/Top\}\}\<\/noinclude\>\n/i","",$x);
	$x = preg_replace("/\<noinclude\>\{\{BotS\/Bottom\}\}\<\/noinclude\>/i","",$x);
	$text[$id] = $x;
}

//Join the two lists together as they already have the status in them
$text['all'] = $text['inactive']."\n".$text['active'];

//Parse the templates
$parsed = parsetemplates($text['all']);

//Start a static list of bots we know about
$bots = array();
//And the two array so that we can sort and remove duplicates before posting (as the parsed array is too complex)
$active = array();
$inactive = array();

//For each template we have parsed
foreach($parsed as $template)
{
	if($template[0] == "BotS")//if it matches our bot template
	{
		array_push($bots,$template[1][1]);//push it to our main array
	}
}

//Now lets get some of the more static lists of bots

//We may as well try and see if we can put any in the active section first
echo "Searching Wikipedia Categorys for bots\n";
$list['cat_active'] = $wiki->categorymembers("Category:Active_Wikipedia_bots",true);
foreach($list['cat_active'] as $botuser)
{
	if( preg_match("/User:/",$botuser) && (strpos($botuser,"/") === FALSE))
	{
		//Get rid of the name space
		$botuser = str_ireplace('User:','',$botuser);
		if(in_array($botuser,$bots) == FALSE)
		{
			array_push($bots,$botuser);
			array_push($parsed,parsetemplates("{{BotS|$botuser||active}}"));
			echo "Added a new bot from Category:Active_Wikipedia_bots  -> $botuser\n";
		}
	}
}

//We can put any that dont claim to be active in the inactive category
$list['cat_all'] = $wiki->categorymembers("Category:All_Wikipedia_bots",true);
foreach($list['cat_all'] as $botuser)
{
	if( preg_match("/User:/",$botuser) && (strpos($botuser,"/") === FALSE))
	{
		$botuser = str_ireplace('User:','',$botuser);
		if(in_array($botuser,$bots) == FALSE)
		{
			array_push($bots,$botuser);
			array_push($parsed,parsetemplates("{{BotS|$botuser||inactive}}"));
			echo "Added a new bot from Category:All_Wikipedia_bots -> $botuser\n";
		}
	}
}

//For each of the bots found
foreach($parsed as $bot)
{
	//usleep(100000);
	//Skip if the template wasnt actually a bot
	if($bot[0] != "BotS"){continue;}
	
	$details = array_fill(1,6,"");
	
	for($i = 1;$i<=6;$i++)
	{
		$details[$i] = trim(str_replace("\n","",$bot[1][$i]));
	}
	
	$details[4] = rtrim($details[4], "\t.");
	
	//Trim stuff
	for($i = 1;$i<=6;$i++)
	{
		$details[$i] = trim(str_replace("\n","",$details[$i]));
	}
	
	$botname = $details[1];
	$botowner = $details[2];
	$status = $details[3];
	$description = $details[4];
	$lastedittimestamp = $wiki->lastedit($botname);
	$lasteditdate = date_parse($lastedittimestamp);
	$interval = date_create('now')->diff(date_create($lastedittimestamp,new DateTimeZone('UTC')));
	$stringmonth = $interval->format('%m months');
	
	
	if($details[3] == "active")
	{
		array_push($active,"{{BotS|$details[1]|$stringmonth}}");
		$count['active']++;
	}
	else if($details[3] == "inactive")
	{
		array_push($inactive,"{{BotS|$details[1]|$stringmonth}}");
		$count['inactive']++;
	}
	else//If we have picked up something else from somewhere
	{
		$details[3] == "inactive";
		//array_push($inactive,"{{BotS|".implode("|",$details)."}}");
		array_push($inactive,"{{BotS|$details[1]|$stringmonth}}");
	}
	
}

//Sort the list
sort($active);
sort($inactive);
//Make sure all elements are unique
$active = array_unique($active);
$inactive = array_unique($inactive);

$output = "";
foreach($active as $row)
{
	$output = $output.$row."\n";
}
foreach($inactive as $row)
{
	$output = $output.$row."\n";
}

$wiki->edit("User:Addshore/Sandbox",$output,"[[User:Addbot|Bot:]] Updating Bot Status Page",true);

/*
//Now lets update the counts (Template:Botstats)
$page['counts'] = "Template:Botstats";
//$text['counts'] = $wiki->getpage($page['counts']);
$post['counts'] = "{{Botstats/Core|all=".$count['all']."|flagged={{NUMBERINGROUP:bot}}|approvedtasks={{PAGESINCATEGORY:Approved Wikipedia bot requests for approval}}|active=".$count['active']."|inactive=".$count['inactive']."}}";
if($count['active'] > 10 && $count['inactive'] > 10 && $count['all'] > 10)
{
	echo "Posting Bot Stats\n";
	//$wiki->edit($page['counts'],$post['counts'],"[[User:Addbot|Bot:]] Updating Bot Stats Template",true);
}else{echo "Stats counts too low to update\n";}
*/

?>