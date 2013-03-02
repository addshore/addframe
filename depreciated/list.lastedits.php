<?PHP

require 'bot.login.php';
global $wiki;

$start = '2013-01-10T00:35:00Z';
//$end = '2013-01-09T22:31:00Z';
$end = '2013-01-08T22:50:00Z';
$list = array();
$done = false;
$limit = 70000;

while($limit > 0)
{


//&ucend=2013-01-08T22:50:00Z&ucstart=2013-01-09T22:50:00Z&ucprop=title|timestamp

	echo "\nSleep 1";
	sleep(1);
	$x = $wiki->query('?action=query&format=php&list=usercontribs&ucuser=Addbot&uclimit=5000&ucnamespace=0&ucstart='.urlencode($start).'&ucend='.urlencode($end).'&ucprop=title|timestamp');
	echo "Extracting pages from request";
	foreach ($x['query']['usercontribs'] as $usercontrib)
	{
		//extract each title to list
		array_push($list,$usercontrib['title']);
		$start = $usercontrib['timestamp'];
	}
	$limit = $limit - 5000;
}

$output = "";
$count = 0;
foreach ($list as $page)
{
	$output = $page."\r\n".$output;
	$count++;
	//echo "\nString has $count";
}
echo "\nString had $count";

echo "\nWriting File";
file_put_contents("/home/addshore/addbot/lastedits.txt",$output);
echo "\nFile Written";
?>