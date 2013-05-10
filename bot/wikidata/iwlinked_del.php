<?php
echo "\nRunning";
//database
require __DIR__.'../../classes/stathat.php';
require __DIR__.'../../config/stathat.cfg';
require __DIR__.'../../classes/database.php';
require __DIR__.'../../config/database.cfg'; 
$db = new Database( $config['dbhost'], $config['dbport'], $config['dbuser'], $config['dbpass'], $config['dbname'], false);
unset($config['dbpass']);

//udpate stat
$res = Database::mysql2array($db->doQuery("select count(*) as count from iwlinked_del;"));
stathat_ez_value($config['stathatkey'], "Addbot - IW Removal - Pending Deletion" , intval($res[0]['count']));

$toget = 200;
$rnd = $res['count(*)'];
if($rnd > 60){$rnd = 60;}
unset($res);

echo "\nSelecting $toget deletions\n";
$c = 0;
while($c<$toget)
{
	$c++;
	$item = Database::mysql2array($db->doQuery('SELECT * from iwlinked_del ORDER BY added ASC LIMIT 1 OFFSET '.rand(0,$rnd)));
	if(count($item) > 0)
	{
		$q = "DELETE FROM iwlinked_del WHERE (lang='".$db->mysqlEscape($item[0]['lang'])."' AND article='".$db->mysqlEscape($item[0]['article'])."')";
		echo "\n$q";
		$res = $db->doQuery($q);
		if( !$res  ){echo "\n".$db->errorStr();}
		
		$res = Database::mysql2array($db->doQuery("SELECT * from iwlinked WHERE (lang='".$db->mysqlEscape($item[0]['lang'])."' AND article='".$db->mysqlEscape($item[0]['article'])."')"));
		if(count($res) > 0)
		{
			$q = "DELETE FROM iwlinked WHERE ";
			foreach($res as $todel)
			{
				$q .= "(id='".$todel['id']."') OR";
			}
			$q = preg_replace('/ OR$/','',$q);//remove final OR
			echo "\n$q";
			$res = $db->doQuery($q);
			if( !$res  ){echo "\n".$db->errorStr();}
		}
	}
	else
	{
		echo "\nReturned none, exiting";
		exit();
	}
}

?>
