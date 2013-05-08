<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');

require 'bot.login.php';
global $wiki;

$tag = "{{Uncategorized|date={{subst:CURRENTMONTHNAME}} {{subst:CURRENTYEAR}}}}";

file_get_contents('http://toolserver.org/~dpl/data/trigger_uncat_update.php');
file_get_contents('http://toolserver.org/~dpl/data/trigger_uncat_file.php');
echo "Sleeping for 5\n";
sleep(5);
$p = file_get_contents('http://toolserver.org/~dpl/data/uncategorized_articles_list.txt');
$p = str_replace(']]','',$p);
$p = str_replace('[[','',$p);
$p = explode("\n",$p);

//For each page found in the list
foreach($p as $page)
{
	sleep(1);
	//Get the text
	$text = $wiki->getpage($page);
	//Make sure the page doesnt contain anything that might mean it has a template
	if($text != "" || preg_match('/\//',$page)) //make sure the page is not empty (deleted)
	{
		if(preg_match("/\[\[Category\:/",$text) == FALSE && preg_match("/stub\}\}/",$text) == FALSE && preg_match("/#REDIRECT ?\[\[.*\]\]/i",$text) == FALSE)
		{
				//Add a new tag by itself
				$text = $tag."\n".$text;
				echo "Adding Uncat tag to $page\n";
				if(strlen($text) > strlen($tag)+5)
				{
					$wiki->edit($page,$text,"[[User:Addbot|Bot:]] Adding Uncategorized tag",true);
				}
				sleep(45);
		}
		else
		{
			echo "SKIPPING $page (gotcat,stub,redirect)\n";
		}
	}
	else
	{
		echo "SKIPPING $page (empty)\n";
	}
}

?>