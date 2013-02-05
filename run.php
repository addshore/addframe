<?
//run.php --page="value"
$options = getopt("",Array("page::"));

echo "loading...";
sleep(1);

// load the classes and stuff
require 'classes/botclasses.php';
require 'classes/database.php';
require 'classes/page.php';
require 'classes/template.php';
require 'config.php';

//Set error reporting level
if(!$config['debug'])
{
	error_reporting(E_ERROR | E_PARSE);
}

// initialise the wiki
$wiki = new wikipedia;
$wiki->url = "http://".$config['url']."/w/api.php";
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
function logevent (string $type, string $what)
{
	//if we are set to log this type
	if(isset($config['Log'][$type]))
	{
		$text = $wiki->getpage('User:Addbot/log/'.$type);// get previous page
		$text = "* ".$what."\n".$text;// add our stuff
		$wiki->edit('User:Addbot/log/'.$type,$text,$what,true);// save the page	
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
	$result = $db->select('pending','*',null,array("LIMIT" => round($count/100+30)));
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
	//make sure we are allowed to edit the page
	if (!$wiki->nobots ($page->getName(),"Addbot",$page->getText())){echo "\n> page has nobots tag..";continue;}
	
	//for reference (User|Wikipedia|FileMediawiki|Template|Help|Category|Portal|Book|Education( |_)program|TimedText)(( |_)talk)?)"
	switch($page->getNamespace()){
		case ""://article
			echo "\n> Is Article";
			//if not a redirect
			if(!$page->isRedirect()	)
			{
		
				//Pre Processing
				$page->preChecks();
				$page->multipleIssues();
				$isorphan = $page->isOrphan();
				$isuncat = $page->isUncat();
				$isdeadend = $page->isDeadend();
				$isreferenced = $page->isReferenced();
			
				//STUB TAG
				echo ".stub";
				if ($page->matches('/\{\{[a-z0-9 _-]*?stub\}\}/'))//if we have a stub tag
				{
					if ($page->wordcount() > 500)//and the word count is over 500
					{
						$page->removeRegex('/\{\{[a-z0-9 _-]*?stub\}\}/i',"Removing {{Stub}}"); echo "-";//remove the stub tag
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
				else if($isdeadend === false)
				{
					$page->removeTag($config['mitag']['deadend']); echo "-";
				}
				
				//UNREFERENCED TAG
				echo ".unref";
				if ($isreferenced === true)
				{
					$page->removeTag($config['mitag']['unreferenced']);
					$page->removeTag($config['mitag']['blpunsourced']);
					echo "-";
				}
				
				//{{Unreferenced}} and {{BLP unsourced}} depending on Category:Living people
				if ($page->matches($config['mitag']['unreferenced']->regexTemplate()) || $page->matches($config['mitag']['blpunsourced']->regexTemplate()))
				{
					if($page->isBLP())
					{
						if($page->matches($config['mitag']['unreferenced']->regexTemplate()))
						{
							$page->removeTag($config['mitag']['unreferenced']);
							$page->addTag($config['mitag']['blpunsourced']);
						}
					}
					else//else not int he cat
					{
						if($page->matches($config['mitag']['blpunsourced']->regexTemplate()))
						{
							$page->removeTag($config['mitag']['blpunsourced']);
							$page->addTag($config['mitag']['unreferenced']);
						}
					}

				}
				
				//NEEDS SECTIONS TAG
				echo ".sec";
				if ($page->needsSections() === false){
					$page->removeTag($config['mitag']['sections']);  echo "-";
				}
				elseif ($page->needsSections() === true){
					$page->addTag($config['mitag']['sections']);  echo "+";
				}
				
				//DEPRECIATED
				echo ".dep";
				$page->removeTag($config['tag']['wikify']);
				
				//TODO: add reflist
				
				//check if has empty section or tag in full section
				echo ".date";
				$page->fixDateTags();// fix any tempaltes that need a date
				
				//If the page has had another significant change
				if($page->hasSigchange() === true)
				{
					//GENERAL CHANGES
					echo ".gen";
					$page->fixTemplates();
					$page->multipleIssues();
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
								logevent("redirect","Can't fix redirect [[".$page->getName()."]] >> [[$target1]] >> [[$target2]]");
							}
						}
					}
				}
				
			}
			break;
			
		case "User talk":
			echo "\n> Is User talk";
			foreach($config['AWB']['usertalk'] as $template)
			{
				//get size before so we can see if we have changed
				$sizebefore = strlen($page->getText());
				//Convert our template to regex
				$regex = str_replace(" ","( |_)",$template);
				$regex = str_ireplace("Template:","(Template:)?",$regex);
				//Do the replace
				$page->setPage(preg_replace('/\{\{'.$template.'/i',"{{subst:$template",$page->getText()));
				//if the page has changed
				if($sizebefore != strlen($page->getText()))
				{
					$page->addSummary("Substing {{[[".$template."]]}}");
				}
			}
			break;
			
		case "Wikipedia":
			echo "\n> Is Wikipedia";
			
			//Wikipedia:AutoWikiBrowser/User talk templates
			if($page->getName() == "Wikipedia:AutoWikiBrowser/User talk templates")//if it is our AWB page
			{
				echo "..awb";
				exec("php /home/addshore/addbot/task.awbtemplates.php");//run the external check
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
	}
	
	//Post
	if($page->hasSigchange() == true)
	{
		echo "\n> POST: ".$page->getSummary();
		//$wiki->edit($page->getName(),$page->getText(),$page->getSummary(),true);
		$wiki->edit("User:Addbot/Sandbox",$page->getText(),$page->getSummary(),true);
		sleep(30);//sleep after an edit
	}
	
	sleep(3);// sleep inbetween requests
}

?>
