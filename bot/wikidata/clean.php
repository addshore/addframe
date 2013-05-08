<?php
//database
require '../../classes/database.php';
require '../../config/database.cfg'; 
require '../../classes/stathat.php';
require '../../config/stathat.cfg';
$db = new Database( $config['dbhost'], $config['dbport'], $config['dbuser'], $config['dbpass'], $config['dbname'], false);
unset($config['dbpass']);

$run = true;
$id = 99999999;

while($run)
{
	$r = Database::mysql2array($db->doQuery("SELECT id,lang,article FROM iwlinked WHERE id<$id ORDER BY id DESC LIMIT 1;"));
	if(count($r) == 0)
	{
		$run = false;
	}
	else
	{

	$id = $r[0]['id'];
	$lang = $r[0]['lang'];
	$article = $r[0]['article'];
	$r = Database::mysql2array($db->doQuery("SELECT * FROM iwlinked WHERE lang='$lang' AND article='$article';"));
	$count = count($r);
	echo "$id $lang:$article Has: $count record(s) ";
	if($count > 1)
	{
		$d = "";
		foreach($r as $row)
		{
			if($row['id'] != $id)
			{
				$d = $d.$row['id']." ";
				$db->doQuery("DELETE FROM iwlinked WHERE id=".$row['id'].";");
			}
		}
		echo "Deleted: $d";
		stathat_ez_count($config['stathatkey'], "Addbot - IW Removal - DB Dupe Removed" , $count);
	}
	echo "\n";
	
	}
}

?>
