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

//error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);
error_reporting(E_ERROR | E_WARNING | E_PARSE);

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
	//$limit = round($count/100+10);
	//if($limit > 200) { $limit = 200; }
	$limit = 222;
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
				
				//Check about removing interwikilinks (will only sig change if over 50)
				$page->interwikilinks();
				
				//If the page has had another significant change
				if($page->hasSigchange() === true)
				{
					//GENERAL CHANGES
					echo ".gen";
					$page->fixWhitespace();
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
					$return = $wiki->edit($page->getName(),$page->getText(),$page->getSummary(),true,true,null,false,$config['General']['maxlag']);
					//$wiki->edit("User:Addbot/Sandbox",$page->getText(),$page->getSummary(),true);
					//sleep(1);//sleep after an edit
				}
			}
		}
	}
	
	//sleep(1);// sleep inbetween requests
}

?>
