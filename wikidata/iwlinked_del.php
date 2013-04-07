<?php
echo "\nRunning";
//database
require '/data/project/addbot/classes/database.php';
require '/data/project/addbot/config/database.php'; 
$db = new Database( $config['dbhost'], $config['dbport'], $config['dbuser'], $config['dbpass'], $config['dbname'], false);
unset($config['dbpass']);

$toget = 100;

echo "\nSelecting $toget deletions\n";
$c = 0;
while($c<$toget)
{
	$c++;
	$item = Database::mysql2array($db->doQuery('SELECT * from iwlinked_del ORDER BY added ASC LIMIT 1 OFFSET '.rand(0,10)));
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
