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
	$page = new Page($item['article'],$wiki);// create our page instance
	$mysqldate = date( 'Y-m-d H:i:s', time() );// get the date
	$recentlychecked = false;
	
	if(!$config['debug'])//if not debuging
	{
		// make sure we havent checked the page in the last 24 hours
		$res = $db->select('checked','*',"article = '".$page->getName()."'");
		if( !$res  ){echo $db->errorStr();} // if no result then say so
		$ret = Database::mysql2array($res);
		if(!empty($ret)){
			if(strtotime($ret[0]['checked']) < strtotime('now -24 hours'))
			{
				echo "Checked ".$page->getName()." less than 24 hours ago\n";
				continue;//skip
			}
		}
	}
	
	echo "Checking ".$page->getName()."\n";
	$page->parse();
	
	//for reference (User|Wikipedia|FileMediawiki|Template|Help|Category|Portal|Book|Education( |_)program|TimedText)(( |_)talk)?)"
	// updated list = http://en.wikipedia.org/wiki/Wikipedia:Namespace
	switch($page->getNamespace()){
		case ""://article
			//check if deadend tag is under a section
			if ($page->isOrphan() === false){ $page->removeTag($config['tag']['orphan']); }
			if ($page->isUncat() === false){ $page->removeTag($config['tag']['uncat']); }
			if ($page->isDeadend() === false){ $page->removeTag($config['tag']['deadend']); }
			if ($page->needsSections() === false){ $page->removeTag($config['tag']['sections']); }
			$page->removeTag($config['tag']['wikify']);
			//check if we can remove the stub tag
			//check if page is unreferenced
			//check if has empty section or tag in full section
			$page->fixDateTags();// fix any tempaltes that need a date
			
			if($page->hasSigchange)// check if a big change has happened to the page
			{
				// do lots of small formating fixes here
				$page->dofixTemplates();
				$page->dofixCitations();
				$page->dofixHTML();
				$page->dofixHyperlinking();
				$page->dofixTypos();
			}
			break;
		case "User talk":
			break;
		case "File":
			if ($page->isPdf() == true){ $page->addTag("BadFormat","(Summary)"); }
			break;
	}
	
	//Post
	$wiki->edit($page->getName()/*$config['sandbox']*/,$page->getText(),$page->getSummary(),true);
	
	//add artile to checked table
	$res = $db->insert($config['tbdone'],array('article' => $page->getName(),'checked' => $mysqldate) ); // inset to database table with time
	if( !$res  ){echo $db->errorStr();} // if no result then say so
	
	sleep(2);// sleep inbetween requests
}

?>