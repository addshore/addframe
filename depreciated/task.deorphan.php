<?PHP
error_reporting(E_ALL);
ini_set('display_errors', '1');

require 'bot.login.php';
global $wiki;

$cat = $argv[1];

$count_removed = 0;
$count_skipped = 0;

$orphans = $wiki->categorymembers($cat,true);
foreach($orphans as $orphan)
{
	if(preg_match("/Category\:/",$orphan) == FALSE)
	{
		//echo exec("python /home/addshore/addbot/pywikipedia/lonelypages.py -page:".$orphan);
		
		//At the moment we can presume the page is still an orphan
		$isorphan = true;
		//Get a list of links to the page in the main space
		sleep(5);
		$links = $wiki->whatlinkshere($orphan,"&blnamespace=0");
		
			foreach($links as $link)
			{
				sleep(3);
				if($isorphan == true)
				{
					//Check the name to see if we can skip
					if(preg_match("/((List|Index) of|\(disambig(uation)?\))/i",$link) == FALSE)
					{
						//Check the text to see if we can skip
						$linktext = $wiki->getpage($link);
						if (preg_match("/(may refer to ?\:|# ?REDIRECT|\{\{Soft ?(Redir(ect)?|link)|\{\{.*((dis(amb?(ig(uation( page)?)?)?)?)(\-cleanup)?|d(big|ab|mbox)|sia|set index( articles)?).*\}\})/i",$linktext) == FALSE)
						{
							$isorphan = false;
						}
					}
				}
			}
		if($isorphan == false)
		{
			//Remove the tag
			sleep(1);
			$text = $wiki->getpage($orphan);
			$text = preg_replace("/\{\{(orp(han)?|Lonely|Do\-attempt)(\| ?(date|att|geo|incat) ?(= ?(January|February|March|April|May|June|July|August|September|October|November|December) ?20[0-9][0-9])? ?){0,4} *\}\}(\r|\n){0,4}/i","",$text);
			if($text != "")
			{
				$wiki->edit($orphan,$text,"[[User:Addbot|Bot:]] Removing Orphan Tag ([[User_talk:Addbot|Report Errors]])",true);
				sleep(15);
				$count_removed++;
			}
			else
			{
				$count_skipped++;
			}
		}
		else
		{
			$count_skipped++;
		}
		
		
	}
}
$log = "User:Addbot/log/orphan";

//$wiki->edit($log,$wiki->getpage($log)."\n|row=".date("Y-m-d h:i:s")."{{!!}}$count_removed{{!!}}$count_skipped{{!!}}[[:$cat]]","[[User:Addbot|Bot:]] Updating Orphan Removal Stats",true);
?>