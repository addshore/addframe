<?PHP
$option = getopt("",Array("file::","lang::"));
require '/data/project/addbot/classes/database.php';
require '/data/project/addbot/config/database.php';
if(!isset($option['lang']))
{die("Incorrect Parameters\n");}
else{
if(!isset($option['file'])){$option['file'] = "/data/project/legoktm/wikidata/".$option['lang']."wiki.txt";}
echo "Adding from ".$option['file']." to ".$option['lang']."\n";}
sleep(10);
$file = file_get_contents($option['file']);
if($file == ""){die("Empty File\n");}
$file = preg_replace("/((\*|#) ?|\[\[:?|\]\])/","",$file); // remove all square brackets (wikilinks)
$list = explode("\n",$file);
echo "Got the list\n";

echo "Connecting to DB...\n";
// connect to the database
$db = new Database( $config['dbhost'], $config['dbport'], $config['dbuser'], $config['dbpass'], $config['dbname'], false);

//If more than 1 returned
if(count($list) > 0 )
{
	$rs = "INSERT INTO iwlinked (lang, article) VALUES ";
	$r = "";
	$c = 0;
	$c2 = 0;
	foreach($list as $item) // for each item
	{
		if( $item != "")
		{
			$c++;
			$r .= "('".$db->mysqlEscape($option['lang'])."','".$db->mysqlEscape($item)."'),";
			//if we have X then INSERT
			if($c >= 100)
			{
				echo "Inserting $c - $c2\n";
				$r = preg_replace('/,$/','',$r);//remove final ,
				$res = $db->doQuery($rs.$r);
				if( !$res  ){echo $db->errorStr();}
				$r = "";//blank the query for the next set
				usleep(1000);
				$c = 0;
				$c2++;
			}
		}
	}
	//If we have any left over and we didnt reach 25
	if($c >= 1)
	{
		echo "Inserting final $c\n";
		$r = preg_replace('/,$/','',$r);//remove final ,
		$res = $db->doQuery($rs.$r);
		if( !$res  ){echo $db->errorStr();}
		$r = "";
	}
}

?>
<?PHP
$option = getopt("",Array("file::","lang::"));
require '/data/project/addbot/classes/database.php';
require '/data/project/addbot/enwiki/config.php';
if(!isset($option['file']) || !isset($option['lang']))
{die("Incorrect Parameters\n");}
else
{echo "Adding from ".$option['file']." to ".$option['lang']."\n";}
sleep(10);
$file = file_get_contents($option['file']);
if($file == ""){die("Empty File\n");}
$file = preg_replace("/((\*|#) ?|\[\[:?|\]\])/","",$file); // remove all square brackets (wikilinks)
$list = explode("\n",$file);
echo "Got the list\n";

echo "Connecting to DB...\n";
// connect to the database
$db = new Database( $config['dbhost'], $config['dbport'], $config['dbuser'], $config['dbpass'], $config['dbname'], false);

//If more than 1 returned
if(count($list) > 0 )
{
	$rs = "INSERT INTO iwlinked (lang, article) VALUES ";
	$r = "";
	$c = 0;
	$c2 = 0;
	foreach($list as $item) // for each item
	{
		if( $item != "")
		{
			$c++;
			$r .= "('".$db->mysqlEscape($option['lang'])."','".$db->mysqlEscape($item)."'),";
			//if we have X then INSERT
			if($c >= 100)
			{
				echo "Inserting $c - $c2\n";
				$r = preg_replace('/,$/','',$r);//remove final ,
				$res = $db->doQuery($rs.$r);
				if( !$res  ){echo $db->errorStr();}
				$r = "";//blank the query for the next set
				usleep(1000);
				$c = 0;
				$c2++;
			}
		}
	}
	//If we have any left over and we didnt reach 25
	if($c >= 1)
	{
		echo "Inserting final $c\n";
		$r = preg_replace('/,$/','',$r);//remove final ,
		$res = $db->doQuery($rs.$r);
		if( !$res  ){echo $db->errorStr();}
		$r = "";
	}
}

?>
