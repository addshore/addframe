<?

//TODO: Enable to be passed just 1 page name to be added to the list

// options for running the file
$shortopts  = "";
$shortopts .= "r::"; // recursive, use if we DONT want recursion when getting cats
$longopts  = array(
    "method:",// method to be used in terms of source
    "source:",// source to be used in method
    "trigger::",// trigger if needed for web
);

// get the options the file was run with
$option = getopt($shortopts, $longopts);

echo "loading...\n";
sleep(1);

// load the classes
require 'botclasses.php';
require 'database.php';

// load the config
require 'config.gen.php';

// initialise the wiki
$wiki = new wikipedia;
$wiki->url = 'http://'.$config['url'].'/w/api.php';
global $wiki;

echo "Get articles from ".$option['source']." using ".$option['method']."\n";
sleep(1);
// get via category members
if(preg_match("/^cat(egory(( |_|-)?members)?)?/i",$option['method'])){
	if(!isset($option['r'])){$recursive = true;}else{$recursive = false;}; // default recursion to true
	$list = $wiki->categorymembers($option['source'],$recursive);
}
// get via a list on a page
elseif(preg_match("/^(page)/i",$option['method'])){
	$text = $wiki->getpage($option['source']); // get the page content
	$text = preg_replace("/(\[\[|\]\])/","",$text); // remove all square brackets (wikilinks)
	$list = explode("\n",$text); // explode into an array we can use
}
// get via transclusions of the source
elseif(preg_match("/^(template|trans(clusions?)?)/i",$option['method'])){
	$list = $wiki->getTransclusions($option['source'],10); // sleep for 10 between requests
}
// get via a web list
elseif(preg_match("/^(web|html)/i",$option['method'])){
	if(isset($option['trigger'])){ file_get_contents($option['trigger']); } // if set get the trigger file
	sleep(30); // sleep for 30 seconds to make sure the page is updated
	$text = file_get_contents($option['source']); // get the content url
	$text = preg_replace("/(\[\[|\]\])/","",$text); // remove all square brackets (wikilinks)
	$list = explode("\n",$text); // explode into an array we can use
}
else{// our regex didnt match a source
	echo "No preset source found\n";
}

// check if the list has been generated and we need to process the stuff below
if(isset($list))
{
	echo "List has been generated, processing...\n";
	sleep(1);

	// after the list has been generated
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
	switch($namespace) {
		// case 0 is different (if an article matches case 0 it will not make it to the list
		case 0:$namespaceregex = "(User|Wikipedia|File|Image|MediaWiki|Template|Help|Category|Portal|Book|Education( |_)Program|TimedText)(( |_)talk)?";break;
		// from here on we are looking to match the articles we want
		case 1:$namespaceregex = "Talk";break;
		case 2:$namespaceregex = "User";break;
		case 3:$namespaceregex = "User( |_)talk";break;
	}

	$final = array(); //define a blank array for our final list

	foreach($list as $item) // for every item we have collected for the list
	{
		usleep(50000);
		if($namespace != 0) // if it is not specificly the main namespace
		{
			if(preg_match("/^".$namespaceregex.":/i",$item)) // get those that match the namespace we want
			{
				array_push($final,$item); // push our article to the final array
			}
		}
		else // we much = 0 (mainspace)
		{
			if(preg_match("/^".$namespaceregex.":/i",$item) == false) // get those that dont match any other namespace
			{
				array_push($final,$item); // push our article to the final array
			}
		}
	}

	echo "Connecting to DB...\n";
	// connect to the database
	$db = new Database( $config['dbhost'], $config['dbport'], $config['dbuser'], $config['dbpass'], $config['dbname'], false);
	foreach($final as $item) // for each item
	{
		sleep(1);
		echo "Adding ".$item." to database\n";
		$res = $db->insert($config['tblist'],array('page' => $item,) ); // inset to database table
		if( !$res  ){echo $db->errorStr(); break;} // if no result then break as we have an error ($db->errorStr())
	}

}
else
{
	echo "Getting list failed!\n";
}

echo "Done\n";

// write to a logfile saying what has happend in regards to the list

?>