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

echo "Connecting to database...\n";
sleep(1);

// connect to the database
$db = new Database( $config['dbhost'], $config['dbport'], $config['dbuser'], $config['dbpass'], $config['dbname'], false);

// get the current list of pending articles
$result = $db->select('pending','*');
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
	$page = new Page(/*$item['article']*/"Dr. Ciriaco Santiago Memorial Award",$wiki);// create our page instance
	
	echo "Checking ".$page->getName()."\n";
	
	//for reference (User|Wikipedia|FileMediawiki|Template|Help|Category|Portal|Book|Education( |_)program|TimedText)(( |_)talk)?)"
	// updated list = http://en.wikipedia.org/wiki/Wikipedia:Namespace
	switch($page->getNamespace()){
		case ""://article
		
			if ($page->isOrphan() === true){ $page->addTag($config['tag']['orphan']); }
			if ($page->isUncat() === true){ $page->addTag($config['tag']['uncat']); }
			if ($page->isDeadend() === true){ $page->addTag($config['tag']['deadend']); }
			
			if ($page->isOrphan() === false){ $page->removeTag($config['tag']['orphan']); }
			if ($page->isUncat() === false){ $page->removeTag($config['tag']['uncat']); }
			if ($page->isDeadend() === false){ $page->removeTag($config['tag']['deadend']); }
			
			if ($page->needsSections() === false){ $page->removeTag($config['tag']['sections']); }
			$page->removeTag($config['tag']['wikify']);
			
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
				$page->fixCitations();
				$page->fixHTML();
				$page->fixHyperlinking();
				$page->fixTypos();
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
			if ($page->isPdf() == true){ $page->addTag("BadFormat","(Summary)"); }
			break;
	}
	
	//Post
	if($page->hasSigchange() == true)//TODO: check page exists before posting
	{
		$wiki->edit(/*$page->getName()*/"User:Addbot/Sandbox",$page->getText(),$page->getSummary(),true);
	}
	
	sleep(1);// sleep inbetween requests
}

?>