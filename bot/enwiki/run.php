<?
//run.php --page="value"
$options = getopt("",Array("page::"));

echo "loading...";
sleep(1);

// load the classes and stuff
require '../../classes/botclasses.php';
require '../../classes/database.php';
require '../../classes/page.php';
require '../../classes/template.php';
require '../../config/database.cfg';
require '../../config/wiki.cfg';

//error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);
error_reporting(E_ERROR | E_WARNING | E_PARSE);

// initialise the wiki
$wiki = new wikipedia;
$wiki->url = "http://en.wikipedia.org/w/api.php";
global $wiki;

echo "\nLogging in...";
sleep(1);echo "..";

// perform the login
$wiki->login($config['user'],$config['password']);
unset($config['password']);
echo "done";

//Get further config stuff
$config = parse_ini_string(preg_replace("/(\<syntaxhighlight lang='ini'\>|\<\/syntaxhighlight\>)/i","",$wiki->getpage("User:Addbot/config")),true);
require 'config.php';//again
require 'config.run.php';
if($config['General']['run'] != true){echo "\nNot set to run"; die();}//if we are not meant to run die
//Get AWB user talk subst list
$awbutt = $wiki->getpage('Wikipedia:AutoWikiBrowser/User_talk_templates');
$awbutt = explode('expand the template(s) on the user talk page.',$awbutt);
$awbutt = str_ireplace(']]','',str_ireplace('# [[','',str_ireplace(']]# [[','|',preg_replace ("/\n/",'',$awbutt[1]))));
$config['AWB']['usertalk'] = explode('|',$awbutt);
unset($awbutt);

//Create log function
//This can be used to post output to User:Addbot/log/<PARAM>
//Data will be added to the top of the page in a bulleted list
function logevent ($type,$what)
{
	global $config,$wiki;
	//if we are set to log this type
	if(isset($config['Log'][$type]))
	{
		$text = $wiki->getpage('User:'.$config['user'].'/log/'.$config['Log'][$type]);// get previous page
		$text = "* ".$what."\n".$text;// add our stuff
		$wiki->edit('User:'.$config['user'].'/log/'.$config['Log'][$type],$text,$what,true);// save the page	
	}
}

//if we were passed an article
if(isset($options['page']))
{
	//add that to the list
	$list = Array(Array("article" => $options['page']));
	echo "\nGot article from options";
}
//else we can go and get the articles from DB
else
{
	// connect to the database
	echo "\nConnecting to database...";
	sleep(1); echo "...";
	$db = new Database( $config['dbhost'], $config['dbport'], $config['dbuser'], $config['dbpass'], $config['dbname'], false);
	echo "done";

	// get the current list of pending articles
	$count = Database::mysql2array($db->select('pending','COUNT(*)'));
	$count = $count[0]['COUNT(*)'];
	echo "\nCurrently ".$count." articles pending review";
	$limit = round($count/100+10);
	if($limit > 200) { $limit = 200; }
	$result = $db->select('pending','*',null,array("LIMIT" => $limit));
	$list = Database::mysql2array($result);
	echo "\nGot ".count($list)." articles from pending";
	if(!$config['debug'])//if not debuging
	{
		// before we start checking we want to remove our got articles from the DB
		// so that another instance wont try and check them also
		echo "\nRemoving";
		sleep(1);
		foreach ($list as $item){
			$res = $db->delete($config['tblist'],array('article' => $item['article']));
			if( !$res  ){echo $db->errorStr();} // if no result then say so
			echo ".";
		}
	}
}

//Check the articles
echo "\nChecking ".count($list)." articles";
sleep(1); echo "..";
foreach ($list as $item)
{
	//Check the article
	echo "\nChecking ".$item['article'];
	// create our page instance
	$page = new Page($item['article'],$wiki);
	//if page size is less than 10 (page doesnt exist) skip
	if (strlen($page->getText()) < 5){echo "\n> Page less than 5 length (may not exist)";continue;}
	
	//for reference (User|Wikipedia|FileMediawiki|Template|Help|Category|Portal|Book|Education( |_)program|TimedText)(( |_)talk)?)"
	switch($page->getNamespace()){
		case ""://article
			echo "\n> Is Article";
			//if not a redirect
			if(!$page->isRedirect()	)
			{
		
				//Pre Processing
				$page->preChecks();
				echo ".date";
				
				//TODO / TOFIX
				//$page->fixDateTags();// fix any tempaltes that need a date
				//$page->fixSectionTags();// add section parameter to any MI template below a section (excludes some)
				
				//The below should be be enabled until a function is created to fix templates unser sections by adding the section parameter correctly
				//$page->multipleIssues();
				//$page->multipleIssuesDupes();
				
				$isorphan = $page->isOrphan();
				$isuncat = $page->isUncat();
				$isdeadend = $page->isDeadend();
				$isreferenced = $page->isReferenced();
			
				//EMPTYSECTIONS
				$max = 10;
				//Check for empty sections that are now the references section
				echo ".emp";
				while ($page->matches('/(={2,7})((?!References)[^=]+)\1(\s+)\1([^=]+)\1/i') && $max > 0)
				{
					$max--;
					$page->setText(preg_replace('/(={2,7})([^=]+)\1(\s+)\1([^=]+)\1/',"$1$2$1\n".$config['tag']['emptysection']->getPost()."\n\n$1$4$1",$page->getText()));
					$page->addSummary("Adding {{".$config['tag']['emptysection']->getName()."}}"); echo"+";
				}
				//if we have an empty references section
				if($page->matches('/(={2,7})((?!References)[^=]+)\1(\s+)\1([^=]+)\1/i'))
				{
					$page->setText(preg_replace('/(={2,7})((?!References)[^=]+)\1(\s+)\1([^=]+)\1/i',"$1$2$1\n"."{{reflist}}"."\n\n$1$4$1",$page->getText()));
					$page->addSummary("Adding {{reflist}}");
				}
			
				//STUB TAG
				echo ".stub";
				if ($page->matches('/\{\{[a-z0-9 _-]*?stub\}\}/'))//if we have a stub tag
				{
					$count = $page->wordcount();
					echo $count;
					if ($count > 500)//and the word count is over 500
					{
						$lead = $page->wordcountlead();
						if($leag > 250)
						{
							$page->removeRegex('/\{\{[a-z0-9 _-]*?stub\}\}/i',"Removing {{Stub}}"); echo "-";//remove the stub tag
						}
					}
				}
			
				//ORPHAN TAG
				echo ".orph";
				if ($isorphan === true)
				{
					$page->addTag($config['mitag']['orphan']); echo "+";
				}
				else if($isorphan === false)
				{
					$page->removeTag($config['mitag']['orphan']); echo "-";
				}
				
				//UNCAT TAG
				echo ".uncat";
				if ($isuncat === true)//if uncat
				{
					if($page->matches('/\{\{[a-z0-9 _-]*?stub\}\}/i'))//and stub
					{
						$page->removeTag($config['mitag']['uncategorized']);
						$page->addTag($config['mitag']['uncategorizedstub']); echo "+";
					}
					else//not stub
					{
						$page->removeTag($config['mitag']['uncategorizedstub']);
						$page->addTag($config['mitag']['uncategorized']); echo "+";
					}
				}
				else if($isuncat === false)//not uncat
				{
					$page->removeTag($config['mitag']['uncategorized']);
					$page->removeTag($config['mitag']['uncategorizedstub']); echo "-";
				}
				
				//DEADEND TAG
				echo ".dead";
				if ($isdeadend === true)
				{
					$page->addTag($config['mitag']['deadend']); echo "+";
				}
				else
				{
					//only try and remove if it is already there
					//(and only try and add underlinked if it was already there)
					if($page->matches('/'.$config['mitag']['deadend']->regexTemplate().'/i'))
					{
						$page->removeTag($config['mitag']['deadend']); echo "-";
						if($isdeadend < 3){
							$page->addTag($config['mitag']['underlinked']); echo "+";
						}
					}
				}
				
				//UNREFERENCED TAG
				echo ".unref";
				//Only perform all of these checks if there is already one of the tags on the page
				if($page->matches('/'.$config['mitag']['unreferenced']->regexTemplate().'/i') || $page->matches('/'.$config['mitag']['blpunsourced']->regexTemplate().'/i'))
				{
					//if there is at least 1
					if ($isreferenced > 0)
					{
						echo "-";
						//are there so many refs that we can just remove it?
						if($isreferenced > 3)
						{
							$page->removeTag($config['mitag']['unreferenced']);
							$page->removeTag($config['mitag']['blpunsourced']);
						}
						//or do we need to change it to {{ref improve}}?
						else
						{
							//now check if it is a BLP to see if we want {{Refimprove}} or {{BLP sources}}
							//Swap if they are currently incorrect
							if($page->isBLP())
							{
								if($page->matches('/'.$config['mitag']['refimprove']->regexTemplate().'/i'))
								{
									$page->removeTag($config['mitag']['refimprove']);
									$page->addTag($config['mitag']['blpsources']);
								}
							}
							else
							{
								if($page->matches('/'.$config['mitag']['blpsources']->regexTemplate().'/i'))
								{
									$page->removeTag($config['mitag']['blpsources']);
									$page->addTag($config['mitag']['refimprove']);
								}
							}
						}
					}
				}
				
				//{{Unreferenced}} and {{BLP unsourced}} depending on Category:Living people
				if ($page->matches('/'.$config['mitag']['unreferenced']->regexTemplate().'/i') || $page->matches('/'.$config['mitag']['blpunsourced']->regexTemplate().'/i'))
				{
					$blp = $page->isBLP();
					if($blp != null)
					{
						if($blp)
						{
							if($page->matches('/'.$config['mitag']['unreferenced']->regexTemplate().'/i'))
							{
								$page->removeTag($config['mitag']['unreferenced']);
								$page->addTag($config['mitag']['blpunsourced']);
							}
						}
						else//else not int he cat
						{
							if($page->matches('/'.$config['mitag']['blpunsourced']->regexTemplate().'/i'))
							{
								$page->removeTag($config['mitag']['blpunsourced']);
								$page->addTag($config['mitag']['unreferenced']);
							}
						}
					}

				}
				
				//NEEDS SECTIONS TAG
				echo ".sec";
				if ($page->needsSections() === false){
					$page->removeTag($config['mitag']['sections']);  echo "-";
				}
				elseif ($page->needsSections() === true){
					//$page->addTag($config['mitag']['sections']);  echo "+";
				}
				
				//DEPRECIATED
				echo ".dep";
				$page->removeTag($config['tag']['wikify']);
				
				//MULTIPLE ISSUES
				$page->multipleIssues();
				
				//Check about removing interwikilinks (will only sig change if over 50)
				$page->interwikilinks();
				
				//If the page has had another significant change
				if($page->hasSigchange() === true)
				{
					//GENERAL CHANGES
					echo ".gen";
					$page->fixTemplates();
					$page->fixGeneral();
					$page->fixWhitespace();
				}
			}
			else
			{
				//else we are a redirect
				echo "..redirect";
				if (preg_match('/# ?REDIRECT ?\[\[(.*?)\]\]/i',$page->getText(),$matches1) == 1)//first redir matchs
				{
					$target1 = $matches1[1];
					echo ">$target1";
					//check if we can find a redirect at our first target
					$text2 = $wiki->getpage($target1);
					if(preg_match('/# ?REDIRECT ?\[\[(.*?)\]\]/i',$text2,$matches2) ==1)//second redir matched
					{
						$target2 = $matches2[1];
						echo ">$target2";
						//do we have a second target?
						if($target2 != "")
						{
							//we must be a double redirect
							echo "..double";
							if($target2 != $target1 && $target2 != $page->getName())
							{
								$page->setText(preg_replace('/# ?REDIRECT ?\[\[(.*?)\]\]/i',"#REDIRECT [[$target2]]",$page->getText()));
								$page->addSummary("Fixing double redirect [[".$page->getName()."]] >> [[$target1]] >> [[$target2]]");
							}
							else
							{
								//Else this is a broken redirect that links to itself in some form of loop
								logevent("redirects","Can't fix redirect [[".$page->getName()."]] >> [[$target1]] >> [[$target2]]");
							}
						}
					}
				}
				
			}
			break;
			
		case "User talk":
			echo "\n> Is User talk";
			if(!preg_match('/\//',$page->getName()))//if it is not a subpage
			{
				foreach($config['AWB']['usertalk'] as $template)
				{
					//get size before so we can see if we have changed
					$sizebefore = strlen($page->getText());
					//Convert our template to regex
					$regex = preg_quote($template,'/');
					$regex = str_replace(" ","( |_)",$regex);
					$regex = preg_replace("/^Template\\\:/i","(Template\:)?",$regex);
					//Do the replace
					if(preg_match('/\{\{'.$regex.'((\|([0-9a-zA-Z _]*?)( ?= ?[0-9a-zA-Z _]*?)){0,6})?\}\}/i',$page->getText(),$matches))
					{
						$new = str_replace("{{","{{Subst:",$matches[0]);
						$page->setText(str_replace($matches[0],$new,$page->getText()));
					}
					//if the page has changed
					if($sizebefore != strlen($page->getText()))
					{
						$page->addSummary("Substing {{[[".$template."]]}}");
					}
				}
			}
			break;
			
		case "Wikipedia":
			echo "\n> Is Wikipedia";
			
			//Wikipedia:AutoWikiBrowser/User talk templates
			if($page->getName() == "Wikipedia:AutoWikiBrowser/User talk templates")//if it is our AWB page
			{
				echo "..awb";
				//exec("php /home/addshore/addbot/task.awbtemplates.php");//run the external check
				break;
			}
			
			
			if($page->isSandbox()){$page->restoreHeader();break;}//check sandboxes
			break;
			
		case "Wikipedia talk":
			if($page->isSandbox()){$page->restoreHeader();break;}//check sandboxes
			break;
			
		case "Template":
			if($page->isSandbox()){$page->restoreHeader();break;}//check sandboxes
			break;
			
		case "Template talk":
			if($page->isSandbox()){$page->restoreHeader();break;}//check sandboxes
			break;
			
		case "File":
			echo "\n> Is File";
			//If a pdf then tag as a pdf
			if ($page->isPdf() == true){
				$page->addTag("badformat","Adding Bad Format");
			}
			break;
			
		case "Category":
			echo "\n> Is Category";
			//Pre processing
			$cats = $wiki->categorymembers($page->getName(),true);
			
			//Manage {{Underpopulated category}}
			echo ".pop";
			//If underpopulatedcategory is not in nowiki tags
			if(!$page->matches('/<nowiki>.*?'.$config['tag']['underpopulatedcategory']->regexTemplate().'.*?</nowiki>/i') || preg_match('/^Category\:Underpopulated .*? categories/i',$page->getName()))
			{
				if(count($cats) > 75)
				{
					$page->removeTag($config['tag']['underpopulatedcategory']); echo "-";
					
				}
				elseif(count($cats) < 10)
				{
					//This was adding the tag to maint cats
					//$page->addTag($config['tag']['underpopulatedcategory']); echo "+";
				}
			}
			break;
	}
	
	//If we have a sig change then we want to post
	if($page->hasSigchange() == true)
	{
		//First lets make sure the page is not protected (we can edit)
		$protection = $wiki->protectionstatus($page->getName());
		$notprotected = true;
		foreach($protection as $inst)
		{
			//check if sysop edit protection is on the page
			if($inst['type'] == "edit" && $inst['level'] == "sysop")
			{
				echo "..protected";
				$notprotected = false;
				logevent("protected","'''Protected''' [[".$page->getName()."]] > ".$page->getSummary());
			}
		}
		
		//if its not protected
		if($notprotected)
		{
			//does it have no bots?
			if (!$wiki->nobots ($page->getName(),"Addbot",$page->getText()))
			{
				echo "..nobots";
				logevent("nobots","'''NoBots''' [[".$page->getName()."]] > ".$page->getSummary());
				continue;
			}
			else
			{
				//if we dont wanrt to skip
				if($page->skip == true)
				{
					echo "\n> SKIP";
					continue;
				}
				else
				{
					//Then we can post
					echo "\n> POST: ".$page->getSummary();
					$wiki->edit($page->getName(),$page->getText(),$page->getSummary(),true,true,null,false,$config['General']['maxlag']);
					//$wiki->edit("User:Addbot/Sandbox",$page->getText(),$page->getSummary(),true);
					sleep(15);//sleep after an edit
				}
			}
		}
	}
	
	sleep(3);// sleep inbetween requests
}

?>
