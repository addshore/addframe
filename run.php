<?
error_reporting(E_ERROR | E_PARSE);

echo "loading...";
sleep(1);

// load the classes and stuff
require 'classes/botclasses.php';
require 'classes/database.php';
require 'classes/page.php';
require 'classes/template.php';
require 'config.php';

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

//create the template instances
foreach ($config['Tags'] as $key=>$tag)
{
	$split = explode(",",$tag);
	$config['tag'][$key] = new Template($split[0],$split);
}

// connect to the database
echo "\nConnecting to database...";
sleep(1); echo "...";
$db = new Database( $config['dbhost'], $config['dbport'], $config['dbuser'], $config['dbpass'], $config['dbname'], false);
echo "done";

// get the current list of pending articles
$result = $db->select('pending','*',null,array("LIMIT" => 10));
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

echo "\nChecking ".count($list)." articles";
sleep(1); echo "..";
foreach ($list as $item)
{

	echo "\nChecking ".$item['article'];
	$page = new Page($item['article'],$wiki);// create our page instance
	if (strlen($page->getText()) < 5){echo "\n> Page less than 5 length (may not exist)";continue;}//if page size is less than 10 (page doesnt exist) skip
	if (!$wiki->nobots ($page->getName(),"Addbot",$page->getText())){echo "\n> page has nobots tag..";continue;}//make sure we are allowed to edit the page
	
	//for reference (User|Wikipedia|FileMediawiki|Template|Help|Category|Portal|Book|Education( |_)program|TimedText)(( |_)talk)?)"
	switch($page->getNamespace()){
		case ""://article
			echo "\n> Is Article";
			//if not a redirect
			if(!$page->matches('/# ?REDIRECT ?\[\[.*?\]\]/i'))
			{
		
				//Pre Processing
				$isorphan = $page->isOrphan();
				$isuncat = $page->isUncat();
				$isdeadend = $page->isDeadend();
				$isreferenced = $page->isReferenced();
			
				//ORPHAN TAG
				echo ".orph";
				if ($isOrphan === true)
				{$page->addTag($config['tag']['orphan']); echo "+";}
				else if($isOrphan === false)
				{$page->removeTag($config['tag']['orphan']); echo "-";}
				
				//UNCAT TAG
				echo ".uncat";
				if ($isuncat === true)
				{$page->addTag($config['tag']['uncategorized']); echo "+";}
				else if($isuncat === false)
				{$page->removeTag($config['tag']['uncategorized']); echo "-";}
				
				//DEADEND TAG
				echo ".dead";
				if ($isdeadend === true)
				{$page->addTag($config['tag']['deadend']); echo "+";}
				else if($isdeadend === false)
				{$page->removeTag($config['tag']['deadend']); echo "-";}
				
				//UNREFERENCED TAG
				echo ".unref";
				if ($isreferenced === true)
				{$page->removeTag($config['tag']['unreferenced']); $page->removeTag($config['tag']['blpunsourced']); echo "-";}
				
				//NEEDS SECTIONS TAG
				echo ".sec";
				if ($page->needsSections() === false){ $page->removeTag($config['tag']['sections']);  echo "-";}
				
				//STUB TAG
				echo ".stub";
				if ($page->matches('/\{\{[a-z0-9 _-]*?stub\}\}/'))//if we have a stub tag
				{
					if ($page->wordcount() > 500)//and the word count is over 500
					{
						$page->removeRegex('/\{\{[a-z0-9 _-]*?stub\}\}/',"Removing {{Stub}}"); echo "-";//remove the stub tag
					}
				}
				
				//DEPRECIATED
				echo ".dep";
				$page->removeTag($config['tag']['wikify']);
				
				//TODO: fix double redirects
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
				echo " > Redirect";
			}
			break;
			
		case "User talk":
			echo "\n> Is User talk";
			//TODO:Subst user talk templates
			break;
			
		case "Wikipedia":
			echo "\n> Is Wikipedia";
			//Wikipedia:AutoWikiBrowser/User talk templates
			if($page->getName() == "Wikipedia:AutoWikiBrowser/User talk templates")//if it is our AWB page
			{
				exec("php /home/addshore/addbot/task.awbtemplates.php");//run the external check
			}
			break;
			
		case "File":
			echo "\n> Is File";
			//If a pdf then tag as a pdf
			if ($page->isPdf() == true){ $page->addTag("badformat","Adding Bad Format"); }
			break;
	}
	
	//Post
	if($page->hasSigchange() == true)
	{
		echo "\n> POST: ".$page->getSummary();
		$wiki->edit(/*$page->getName()*/"User:Addbot/Sandbox",$page->getText(),$page->getSummary(),true);
		sleep(30);//sleep after an edit
	}
	
	sleep(3);// sleep inbetween requests
}

?>