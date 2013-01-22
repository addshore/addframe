<?

// make sure we actually have an argument or else explain what they must input

// load the classes
require 'botclasses.php';
require 'database.php';

// load the config
require 'config.gen.php';

// initialise the wiki
$wiki = new wikipedia;
$wiki->url = 'http://'.$config['url'].'/w/api.php';
global $wiki;

//CASE SELECT $type

//CATEGORY
	if(!isset($recursive)){$recursive = true}; // default recursion to true
	$list = $wiki->categorymembers($category,$recursive);

//PAGE
	$text = $wiki->getpage($page); // get the page content
	$text = preg_replace("/(\[\[|\]\])/","",$text); // remove all square brackets (wikilinks)
	$list = explode("\n",$text); // explode into an array we can use

//TEMPLATE
	$list = $wiki->getTransclusions($template,10); // sleep for 10 between requests

//HTML
	//get the main page (this could be with or without wikilinks) pages broken with linbreaks
	if(isset($trigger)){ file_get_contents($trigger); } // if set get the trigger file
	sleep(30); // sleep for 30 seconds to make sure the page is updated
	$text = file_get_contents($url); // get the content url
	$text = preg_replace("/(\[\[|\]\])/","",$text); // remove all square brackets (wikilinks)
	$list = explode("\n",$text); // explode into an array we can use

	
//After the list has been generated
	$list = array_unique($list); // make sure all of the elements is unique
	if(!isset($namespace)){$namespace = 0;} // default namespace is 0 (article)
	/* Used for reference (en.wikipedia)
	0	Main		Talk			1
	2	User		User talk		3
	4	Wikipedia	Wikipedia talk	5
	6	File		File talk		7
	8	MediaWiki	MediaWiki talk	9
	10	Template	Template talk	11
	12	Help		Help talk		13
	14	Category	Category talk	15
	100	Portal		Portal talk		101
	108	Book		Book talk		109
	446	Education Program	Education Program talk	447
	710	TimedText			TimedText talk			711
	*/
	
	//restrict the namespace depending on $namespace (presume article namespace only if not set)
	$final = list
	
//NOW either connect to db and load all of the pages into the table (making sure we dont add duplicates)
//OR write to a simple text file (appending to the list currently (making sure we dont add duplicates)

//write to logfile saying the list was generated log.txt?
?>