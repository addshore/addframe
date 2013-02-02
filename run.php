<?
// Report all errors except E_NOTICE
error_reporting(E_ALL ^ E_NOTICE);

echo "loading...\n";
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

echo "Logging in...\n";
sleep(1);

// perform the login
$wiki->login($config['user'],$config['password']);
unset($config['password']);

//Get further config stuff
eval(preg_replace("/(\<syntaxhighlight lang='php'\>|\<\/syntaxhighlight\>)/i","",$wiki->getpage("User:Addbot/config")));//run the onwiki config
if($config['run'] == false){die();}//if we are not meant to run die

echo "Connecting to database...\n";
sleep(1);

// connect to the database
$db = new Database( $config['dbhost'], $config['dbport'], $config['dbuser'], $config['dbpass'], $config['dbname'], false);

// get the current list of pending articles
$result = $db->select('pending','*',null,0,10);
$list = Database::mysql2array($result);

if(!$config['debug'])//if not debuging
{
	// before we start checking we want to remove our got articles from the DB
	// so that another instance wont try and check them also
	echo "Removing ".count($list)." articles from pending\n";
	foreach ($list as $item){
		$res = $db->delete($config['tblist'],array('article' => $item['article']));
		if( !$res  ){echo $db->errorStr();} // if no result then say so
	}
}

echo "Checking ".count($list)." articles\n";
sleep(1);
foreach ($list as $item)
{

	$page = new Page($item['article'],$wiki);// create our page instance
	if (strlen($page->getText()) < 10){continue;}//if page size is less than 10 (page doesnt exist) skip
	if ($wiki->nobots ($page->getName(),"Addbot",$page->getText())){continue;}//make sure we are allowed to edit the page
	echo "Checking ".$page->getName()."\n";
	
	//for reference (User|Wikipedia|FileMediawiki|Template|Help|Category|Portal|Book|Education( |_)program|TimedText)(( |_)talk)?)"
	switch($page->getNamespace()){
		case ""://article
		
			//Pre Processing
			$isorphan = $page->isOrphan();
			$isuncat = $page->isUncat();
			$isdeadend = $page->isDeadend();
		
			//ORPHAN TAG
			if ($isOrphan === true)
			{$page->addTag($config['tag']['Orphan']);}
			else if($isOrphan === false)
			{$page->removeTag($config['tag']['Orphan']);}
			
			//UNCAT TAG
			if ($isuncat === true)
			{$page->addTag($config['tag']['Uncategorized']);}
			else if($isuncat === false)
			{$page->removeTag($config['tag']['Uncategorized']);}
			
			//DEADEND TAG
			if ($isdeadend === true)
			{$page->addTag($config['tag']['Deadend']);}
			else if($isdeadend === false)
			{$page->removeTag($config['tag']['Deadend']);}
			
			//NEEDS SECTIONS TAG
			if ($page->needsSections() === false){ $page->removeTag($config['tag']['Sections']); }
			
			//STUB TAG
			if ($page->matches('/\{\{[a-z0-9 _-]*?stub\}\}/'))//if we have a stub tag
			{
				if(!$page->matches('/('.implode('|',$config['ignore']['stub']).')/i'))//and its not on the ignore list
				{
					if ($page->wordcount() > 500)//and the word count is over 500
					{
						$page->removeRegex('/\{\{[a-z0-9 _-]*?stub\}\}/',"Removing Stub Tag");//remove the stub tag
					}
				}
			}
			
			//DEPRECIATED
			$page->removeTag($config['tag']['Wikify']);
			
			//TODO: fix double redirects
			//TODO: add reflist
			
			//check if page is unreferenced
			//check if has empty section or tag in full section
			$page->fixDateTags();// fix any tempaltes that need a date
			
			//If the page has had another significant change
			if($page->hasSigchange() === true)
			{
				//GENERAL CHANGES
				$page->fixTemplates();
				$page->multipleIssues();
				$page->fixWhitespace();
				$page->fixGeneral();
			}
			break;
			
		case "User talk":
			//TODO:Subst user talk templates
			break;
			
		case "Wikipedia":
			if($page->getName() == "Wikipedia:AutoWikiBrowser/User talk templates")//if it is our AWB page
			{
				exec("php /home/addshore/addbot/task.awbtemplates.php");//run the external check
			}
			break;
			
		case "File":
			//If a pdf then tag as a pdf
			if ($page->isPdf() == true){ $page->addTag("Bad format","(Summary)"); }
			break;
	}
	
	//Post
	if($page->hasSigchange() == true)
	{
		$wiki->edit($page->getName(),$page->getText(),$page->getSummary(),true);
		sleep(30);//sleep after an edit
	}
	
	sleep(2);// sleep inbetween requests
}

?>