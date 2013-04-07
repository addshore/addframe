<?php
echo "\nRunning";
//database
require '/data/project/addbot/classes/stathat.php';
require '/data/project/addbot/config/stathat.php';
require '/data/project/addbot/classes/database.php';
require '/data/project/addbot/config/database.php'; 
$db = new Database( $config['dbhost'], $config['dbport'], $config['dbuser'], $config['dbpass'], $config['dbname'], false);
unset($config['dbpass']);

//udpate stat
$res = Database::mysql2array($db->doQuery("select count(*) as count from iwlinked_del;"));
stathat_ez_value($config['stathatkey'], "Addbot - IW Removal - Pending Deletion" , intval($res[0]['count']));
unset($res);

$toget = 200;
echo "\nSelecting $toget deletions\n";
$c = 0;
while($c<$toget)
{
	$c++;
	$item = Database::mysql2array($db->doQuery('SELECT * from iwlinked_del ORDER BY added ASC LIMIT 1 OFFSET '.rand(0,100)));
	$q = "DELETE FROM iwlinked_del WHERE (lang='".$db->mysqlEscape($item[0]['lang'])."' AND article='".$db->mysqlEscape($item[0]['article'])."')";
	echo "\n$q";
	$res = $db->doQuery($q);
	if( !$res  ){echo "\n".$db->errorStr();}
	$q = "DELETE FROM iwlinked WHERE (lang='".$db->mysqlEscape($item[0]['lang'])."' AND article='".$db->mysqlEscape($item[0]['article'])."')";
	echo "\n$q";
	$res = $db->doQuery($q);
	if( !$res  ){echo "\n".$db->errorStr();}
}

?>
