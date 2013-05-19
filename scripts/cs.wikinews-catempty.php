<?
//Classes and configs
require '/data/project/addbot/classes/botclasses.php';
require '/data/project/addbot/config/wiki.php';

//and wikidata
$wiki      = new wikipedia;
$wiki->url = "http://cs.wikinews.org/w/api.php";
global $wiki;
echo "\nLogging in to cs.wikinews.org...";
$wiki->login("", "");

$reason = "Nahrazeno nominativní kategorií";

$pages = $wiki->categorymembers("Kategorie:Podle dne");
echo count($pages)."\n";
foreach($pages as $page)
{
	echo "$page\n";
	//Uncomment the line below to actually perform the operation
	//$wiki->delete($page,$reason);
}
?>
