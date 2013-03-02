<?PHP
error_reporting(E_ALL);
ini_set('display_errors', '1');

require 'bot.login.php';
global $wiki;

$list = $wiki->categorymembers("Category:All_articles_needing_sections",true);

$log = "";
$logcount = 0;

	foreach($list as $page)
	{
	$largestsection = 0;
	$sectioncount = 0;
		//Make sure we are working in the mainspace
		if (preg_match('/:/',$page) == FALSE )
		{
			echo "checking $page\n";
			sleep(2);
			$text = $wiki->getpage($page);
			//Make sure our tag is not below a section
			if(preg_match('/==.*?\{\{((cleanup|needs ?)?Sections)(\| ?(date) ?(= ?(January|February|March|April|May|June|July|August|September|October|November|December) ?20[0-9][0-9])? ?){0,1}\}\}(\r\n|\n\n){0,2}/is',$text) != 0)
			{
				$text = preg_replace("/\{\{((cleanup|needs ?)?Sections)/i","{{Sub-sections",$text);
				$wiki->edit($page,$text,"[[User:Addbot|Bot:]] Removing Sections Tag, Replacing with Sub-Sections tag ([[User_talk:Addbot|Report Errors]])",true);
				echo "Change to subsections";
				sleep(15);
				break;
			}
			
			//Match each of the sections
			preg_match_all('/\n==(=)? ?.* ?===?/i',$text, $sections, PREG_PATTERN_ORDER);
			$split = preg_split('/\n==(=)? ?.* ?===?/i',$text);
			
			//$largestsection = 0;
			
			foreach($split as $id => $section)
			{
				echo "ID = ".$id."\n";
				//if it is the main section
				if($id == 0)
				{
					$largestsection = strlen($section);
					$sectioncount++;
				}
				else
				{
					if (preg_match('/See ?also|(external( links)?|references|notes|bibliography|further( reading)?)/i',$sections[0][$id-1]) == 0)
					{
						echo "-- IS a valid section per ".$sections[0][$id-1]." \n";
						if(strlen($section) > $largestsection)
						{
							$largestsection = strlen($section);
						}
						$sectioncount++;
					}
				}
			}
			
			//Has enough sections to remove the tag
			if($sectioncount >= 4 && $largestsection <= 5000)//was 2750 for AVG
			{
				echo "CAN REMOVE TAG on $page\n";
				$text = preg_replace("/\{\{((cleanup|needs ?)?Sections)(\| ?(date) ?(= ?(January|February|March|April|May|June|July|August|September|October|November|December) ?20[0-9][0-9])? ?){0,1} *\}\}(\r\n|\n\n){0,3}/i","",$text);
				if($text != "")
				{
					$wiki->edit($page,$text,"[[User:Addbot|Bot:]] Removing Sections Tag - $sectioncount sections with a max size of $largestsection",true);
					sleep(15);
				}
			}
			//now 4 as we include the lead
			elseif($sectioncount >= 4 && $largestsection <= 10000)
			{
				echo "If it is close then add to a list\n";
				$logcount++;
				$log = $log."|row".$logcount."=[[$page]]{{!!}}$sectioncount{{!!}}".strlen($text)."{{!!}}".strlen($text)/$sectioncount."\n";
			}
		}
		
	}

//Add the table to the log
echo "Post the final list\n";
$log = '{{Table
|type=class="wikitable sortable"
|title=Borderline {{tl|Sections}} removals not done by [[User:Addbot]] (4 sections, largest is 10,000)
|hdrs=Article!!Distinct Sections!!Page Size!!Average Size!!Largest Size'."\n".$log;
$log = $log."\n}}";
sleep(15);
$wiki->edit("User:Addbot/log/sections",$log,"[[User:Addbot|Bot:]] Posting Analysis Results",true);
?>