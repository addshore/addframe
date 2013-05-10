<?PHP
$option = getopt("",Array("file::","lang::"));
require __DIR__.'/../../classes/database.php';
require __DIR__.'/../../config/database.cfg';
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
	$rs = "INSERT INTO iwlink (lang, article) VALUES ";
	$r = "";
	$c = 0;
	$c2 = 0;
	foreach($list as $item) // for each item
	{
		if( $item != "")
		{
			//check if the item needs to be added (or is it already there..?)
			/*
			$chk = $db->doQuery("SELECT Count(*) as Count FROM iwlinked WHERE lang='".$option['lang']."' AND article='".$db->mysqlEscape($item)."'");
			if( !$chk  ){echo $db->errorStr();}
			$chkres = Database::mysql2array($chk);
			if(intval($chkres[0]['Count']) == 0)
			{
			*/
				echo "+";
				$c++;
				$r .= "('".$db->mysqlEscape($option['lang'])."','".$db->mysqlEscape($item)."'),";
				//if we have X then INSERT
				if($c >= 100)
				{
					echo "\nInserting $c - $c2\n";
					$r = preg_replace('/,$/','',$r);//remove final ,
					$res = $db->doQuery($rs.$r);
					if( !$res  ){
					echo $db->errorStr();
					}
					$r = "";//blank the query for the next set
					usleep(1000);
					$c = 0;
					$c2++;
				}
			/*
			}
			else
			{
				echo "-";
			}
			*/
		}
	}
	//If we have any left over and we didnt reach 25
	if($c >= 1)
	{
		echo "\nInserting final $c\n";
		$r = preg_replace('/,$/','',$r);//remove final ,
		$res = $db->doQuery($rs.$r);
		if( !$res  ){echo $db->errorStr();}
		$r = "";
	}
}

?>