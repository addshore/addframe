#! /usr/bin/php

<?PHP
error_reporting(E_ALL ^ E_NOTICE);
ini_set('display_errors', 1);

//From http://toolserver.org/~chris/highlight.php?d=chris/classes/&f=botclasses.php
require __DIR__.'/../classes/botclasses.php';

$wiki = new wikipedia;
$wiki->url = 'http://en.wikipedia.org/w/api.php';
global $wiki;

$parentpid = posix_getpid();

$user = "Addbot";
$nickname = "Addbot";
$owner = "Addshore";

$mysandbox = "User:".$owner."/Sandbox";

set_time_limit(0); 
require __DIR__.'/../config/wiki.cfg';
$wiki->login($config['user'],$config['password']);
echo "USER: Logged In!\n";
unset($config['password']);



global $wiki;

$cat = 'Category:Orphaned articles';

$count_removed = 0;
$count_skipped = 0;

$orphans = $wiki->categorymembers($cat,true);
foreach($orphans as $orphan)
{
	if(preg_match("/Category\:/",$orphan) == FALSE)
	{
		//At the moment we can presume the page is still an orphan
		$isorphan = true;
		$cause = "";
		
		//Get a list of links to the page in the main space		
		$links = $wiki->whatlinkshere($orphan,"&blnamespace=0&blfilterredir=nonredirects&bllimit=500");
		
			foreach($links as $link)
			{
				//sleep(3);
				if($isorphan == true)
				{
					//Check the name to see if we can skip
					if(preg_match("/((List|Index) of|\(disambig(uation)?\))/i",$link) == FALSE)
					{
						//Check the text to see if we can skip
						$linktext = $wiki->getpage($link);
						if (preg_match("/(may refer to ?\:|# ?REDIRECT|\{\{Soft ?(Redir(ect)?|link)|\{\{.*((dis(amb?(ig(uation( page)?)?)?)?)(\-cleanup)?|d(big|ab|mbox)|sia|set index( articles)?).*\}\})/i",$linktext) == FALSE)
						{
							//The below if fixes the bug of recognising itself as a link if nothing else is returned...
							if($link != $orphan)
							{
								echo "$link << IS NOT ORPHAN\n";
								$isorphan = false;
								$cause = $link;
							}
						}
					}
				}
			}
		if($isorphan == false)
		{
			//Remove the tag
			$text = $wiki->getpage($orphan);
			if($wiki->nobots($orphan,$user,$text))
			{
				$text = preg_replace("/\{\{(orp(han)?|Lonely|Do\-attempt)(\| ?(date|att|geo|incat)? ?(= ?(January|February|March|April|May|June|July|August|September|October|November|December) ?20[0-9][0-9])? ?){0,4} *\}\}(\r|\n){0,4}/i","",$text);
				if($text != "")
				{
					echo "    EDIT $orphan\n";
					$wiki->edit($orphan,$text,"[[User:Addbot|Bot:]] Removing Orphan Tag - Linked from [[$cause]] ([[User_talk:Addbot|Report Errors]])",true);
				}
			}
		}
		
		
	}
}
$log = "User:Addbot/log/orphan";

?>

