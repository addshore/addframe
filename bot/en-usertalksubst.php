#! /usr/bin/php5

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

//Get the list of templates and strip away the rubbish
$awbutt = explode('expand the template(s) on the user talk page.',$wiki->getpage('Wikipedia:AutoWikiBrowser/User_talk_templates'));
$awbutt = str_ireplace(']]','',str_ireplace('# [[','',str_ireplace(']]# [[','|',preg_replace ("/\n/",'',$awbutt[1]))));
//$awbutt = str_ireplace('<noinclude>{{pp-semi-protected|small=yes}}</noinclude>{{AWB}}{{shortcut|WP:AWB/UTT}}This page contains templates that AWB will automatically substitute on user talk pages as part of the AWB general fixes. Please make sure that general fixes are enabled, and this will be done when processing user talk pages. The MediaWiki API is used to automatically substitute and expand the template(s) on the user talk page.).','',$awbutt);
$awbutt = explode('|',$awbutt);
echo "Got the list\n";

//check each template
foreach ($awbutt as $template)
{
	//sleep(1);
	echo "Geting Transclusions for $template\n";
	//Get the pages the template is found on
	$pages = $wiki->getTransclusions($template,null,"&einamespace=3");
	//print_r($pages);
	foreach ($pages as $page)
	{
		//If it is a user talk page and is not a sub page
		if ( (strpos($page,"User talk:") !== FALSE) && (strpos($page,"/") === FALSE))
		{
		
			//Get the content and try to subst the template
			$text = $wiki->getpage($page);
			
			//Skip if the user wants us to
			if($wiki->nobots($page,$user,$text) != true){echo "Skipped $page due to {{nobots}}\n";continue;}
			
			
//Set main vars
                                $subst1 = $template;                                        //As in list
                                $subst1a = str_ireplace('_',' ',$subst1);          //1 _ to SPACE
                                $subst1b = str_ireplace(' ','_',$subst1);          //1 SPACE to _
 
 
//Subst all as they are in list with _ as SPACE and SPACE as _
                                $text = str_ireplace("{{$subst1}}","{subst:$subst1}",$text);
                                $text = str_ireplace('{{'.$subst1.'|','{{subst:'.$subst1.'|',$text);
                                $text = str_ireplace("{{$subst1a}}","{subst:$subst1a}",$text);
                                $text = str_ireplace('{{'.$subst1a.'|','{{subst:'.$subst1a.'|',$text);
                                $text = str_ireplace("{{$subst1b}}","{subst:$subst1b}",$text);
                                $text = str_ireplace('{{'.$subst1b.'|','{{subst:'.$subst1b.'|',$text);
 
//If origional contains Template: subst all without it also
                                if( preg_match("/Template:/",$template))
                                {
                                        $templaten = str_ireplace('Template:','',$template);
 
                                        $subst2 = $templaten;                               //rm - Template:
                                        $subst2a = str_ireplace('_',' ',$subst2);          //2 _ to SPACE
                                        $subst2b = str_ireplace(' ','_',$subst2);          //2 SPACE to _
 
//Subst all minus Template: with _ as SPACE and SPACE as _
                                        $text = str_ireplace("{{$subst2}}","{subst:$subst2}",$text);
                                        $text = str_ireplace('{{'.$subst2.'|','{{subst:'.$subst2.'|',$text);
                                        $text = str_ireplace("{{$subst2a}}","{subst:$subst2a}",$text);
                                        $text = str_ireplace('{{'.$subst2a.'|','{{subst:'.$subst2a.'|',$text);
                                        $text = str_ireplace("{{$subst2b}}","{subst:$subst2b}",$text);
                                        $text = str_ireplace('{{'.$subst2b.'|','{{subst:'.$subst2b.'|',$text);
                                }
			
			
			
			
			echo "Attempting to Subst $template on $page\n\r";
			$wiki->edit($page,$text,"[[User:Addbot|Bot:]] Substing template {{[[$template]]}}",true);
			sleep(30);
		}
	}
}
 
?>

