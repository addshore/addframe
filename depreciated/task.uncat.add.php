<?php

error_reporting(E_ALL ^ E_NOTICE);
ini_set('display_errors', 1);

//From http://toolserver.org/~chris/highlight.php?d=chris/classes/&f=botclasses.php
require '/data/project/addbot/classes/botclasses.php';

$wiki = new wikipedia;
$wiki->url = 'http://en.wikipedia.org/w/api.php';
global $wiki;

$parentpid = posix_getpid();

$user = "Addbot";
$nickname = "Addbot";
$owner = "Addshore";

$mysandbox = "User:".$owner."/Sandbox";

set_time_limit(0); 
require '/mnt/secure/addshore/.password.addbot'; 
$wiki->login($user,$config['password']);
echo "USER: Logged In!\n";
unset($config['password']);



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