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
	sleep(1);
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
	// updated list = http://en.wikipedia.org/wiki/Wikipedia:Namespace
	switch($page->getNamespace()){
		case ""://article
		
			if ($page->isOrphan() === true){ $page->addTag($config['tag']['Orphan']); }
			if ($page->isUncat() === true){ $page->addTag($config['tag']['Uncategorized']); }
			if ($page->isDeadend() === true){ $page->addTag($config['tag']['Deadend']); }
			
			if ($page->isOrphan() === false){ $page->removeTag($config['tag']['Orphan']); }
			if ($page->isUncat() === false){ $page->removeTag($config['tag']['Uncategorized']); }
			if ($page->isDeadend() === false){ $page->removeTag($config['tag']['Deadend']); }
			
			if ($page->needsSections() === false){ $page->removeTag($config['tag']['Sections']); }
			$page->removeTag($config['tag']['Wikify']);
			
			//TODO: - stubs
			//TODO: fix double redirects
			//TODO: add reflist
			
			//check if page is unreferenced
			//check if has empty section or tag in full section
			$page->fixDateTags();// fix any tempaltes that need a date
			
			if($page->hasSigchange() === true)// check if a big change has happened to the page
			{
				// do lots of small formating fixes here
				$page->fixTemplates();
				$page->multipleIssues();
				$page->fixWhitespace();
			}
			break;
		case "User talk":
			//TODO:Subst user talk templates
			break;
		case "Wikipedia":
			//TODO:Update AWB talk template subst list
			break;
		case "File":
			if ($page->isPdf() == true){ $page->addTag("Bad format","(Summary)"); }
			break;
	}
	
	//Post
	if($page->hasSigchange() == true)//TODO: check page exists before posting
	{
		$wiki->edit($page->getName(),$page->getText(),$page->getSummary(),true);
		sleep(60);
	}
	
	sleep(2);// sleep inbetween requests
}

?>