<?
// Report all errors except E_NOTICE
error_reporting(E_ALL ^ E_NOTICE);

echo "loading...";
sleep(1);

// load the classes and stuff
require 'classes/botclasses.php';
require 'classes/database.php';
require 'classes/page.php';
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
$config = parse_ini_string(file_get_contents(preg_replace("/(\<syntaxhighlight lang='php'\>|\<\/syntaxhighlight\>)/i","",$wiki->getpage("User:Addbot/config"))),true);
if($config['run'] != true){echo "\nNot set to run"; die();}//if we are not meant to run die

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
	if (strlen($page->getText()) < 10){echo "\n> Page less than 10 length..";continue;}//if page size is less than 10 (page doesnt exist) skip
	if (!$wiki->nobots ($page->getName(),"Addbot",$page->getText())){echo "\n> page has nobots tag..";continue;}//make sure we are allowed to edit the page
	
	//for reference (User|Wikipedia|FileMediawiki|Template|Help|Category|Portal|Book|Education( |_)program|TimedText)(( |_)talk)?)"
	switch($page->getNamespace()){
		case ""://article
			echo "\n> Is Article";
		
			//Pre Processing
			$isorphan = $page->isOrphan();
			$isuncat = $page->isUncat();
			$isdeadend = $page->isDeadend();
			$isreferenced = $page->isReferenced();
		
			//ORPHAN TAG
			echo ".orph";
			if ($isOrphan === true)
			{$page->addTag($config['tag']['Orphan']); echo "+";}
			else if($isOrphan === false)
			{$page->removeTag($config['tag']['Orphan']); echo "-";}
			
			//UNCAT TAG
			echo ".uncat";
			if ($isuncat === true)
			{$page->addTag($config['tag']['Uncategorized']); echo "+";}
			else if($isuncat === false)
			{$page->removeTag($config['tag']['Uncategorized']); echo "-";}
			
			//DEADEND TAG
			echo ".dead";
			if ($isdeadend === true)
			{$page->addTag($config['tag']['Deadend']); echo "+";}
			else if($isdeadend === false)
			{$page->removeTag($config['tag']['Deadend']); echo "-";}
			
			//UNREFERENCED TAG
			echo ".unref";
			if ($isreferenced === true)
			{$page->removeTag($config['tag']['Unreferenced']); $page->removeTag($config['tag']['BLP unsourced']); echo "-";}
			
			//NEEDS SECTIONS TAG
			echo ".sec";
			if ($page->needsSections() === false){ $page->removeTag($config['tag']['Sections']);  echo "-";}
			
			//STUB TAG
			echo ".stub";
			if ($page->matches('/\{\{[a-z0-9 _-]*?stub\}\}/'))//if we have a stub tag
			{
				if ($page->wordcount() > 500)//and the word count is over 500
				{
					$page->removeRegex('/\{\{[a-z0-9 _-]*?stub\}\}/',"Removing Stub Tag"); echo "-";//remove the stub tag
				}
			}
			
			//DEPRECIATED
			echo ".dep";
			$page->removeTag($config['tag']['Wikify']);
			
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
				$page->fixWhitespace();
				$page->fixGeneral();
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
			if ($page->isPdf() == true){ $page->addTag("Bad format","(Summary)"); }
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