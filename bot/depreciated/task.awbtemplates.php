<?PHP

require 'bot.login.php';

//Set the page variables
$page = "Wikipedia:AutoWikiBrowser/User_talk_templates";
$output = "<noinclude>{{pp-semi-protected|small=yes}}</noinclude>\n{{AWB}}\n{{shortcut|WP:AWB/UTT}}\n[[pt:Wikipedia:AutoWikiBrowser/User talk templates]]\nThis page contains templates that AWB will automatically substitute on user talk pages as part of the AWB general fixes. Please make sure that general fixes are enabled, and this will be done when processing user talk pages. The MediaWiki API is used to automatically substitute and expand the template(s) on the user talk page.";
$count_rem = 0;
$count_add = 0;

//Firstly lets see if we have run in the past 24 hours
$date = date_create();
$currdate = date_parse(date_format($date, 'Y-m-d\TH:i:s\Z'));
$lastdate = date_parse($wiki->lasteditonpage("Addbot",$page));

//Get the list of templates and strip away the rubbish
$awbutt = explode('expand the template(s) on the user talk page.',$wiki->getpage($page));
$awbutt = str_ireplace(']]','',str_ireplace('# [[','',str_ireplace(']]# [[','|',preg_replace ("/\n/",'',$awbutt[1]))));
$awbutt = explode('|',$awbutt);
$xcount = count($awbutt);

//Get templates from categorys
$cat = $wiki->categorymembers("Category:Wikipedia_templates_to_be_automatically_substituted",true);
$templates = array_merge($awbutt,$cat);
$cat = $wiki->categorymembers("Category:WikiProject-specific_welcome_templates",true);
$templates = array_merge($templates,$cat);


//Make sure all elements are unique
$templates = array_unique($templates);

//Below we are about to make the bot check for redirects also
//(there is no point in checking for redirects from pages that are redirects though)

//Get the redirects to any page that isnt already a redirect
$redirects = array();
	foreach($templates as $template)
	{
		//Get redirects to this template in the template namespace
		usleep(10000);
		echo "Geting redirects for $template\n";
		$redirects = array_merge($redirects,$wiki->whatlinkshere($template,"&blfilterredir=redirects&blnamespace=10"));
	}
$templates = array_merge($templates,$redirects);

//Sort the list
sort($templates);
//Make sure all elements are unique
$templates = array_unique($templates);
//$ycount = count($templates);

//For every page found in the list
	foreach($templates as $template)
	{
		usleep(10000);
		echo "Checking $template\n";
		//If the page exists
		if($wiki->getpageid($template) > 0)
		{
			//Add the page to the list
			if(preg_match("/\/(sandbox.?|doc|\.(css|js))/i",$template) != true)
			{
				$output .= "\n# [[$template]]";
				$ycount++;
			}
		}
		else
		{
			sleep(10);
				//If the page exists
				if($wiki->getpageid($template) > 0)
				{
					//Add the page to the list
					if(preg_match("/\/(sandbox.?|doc|\.(css|js))/i",$template) != true)
					{
						$output .= "\n# [[$template]]";
						$ycount++;
					}
				}
				else
				{
					sleep(10);
					$count_rem++;
				}
		}
	}

$count_add = $ycount - $xcount;

echo "Posting new list\n";
$wiki->edit($page,$output,"[[User:Addbot|Bot:]] Removing $count_rem red links, Adding $count_add links, Sorting",true);
?>
