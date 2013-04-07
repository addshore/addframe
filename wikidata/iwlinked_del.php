<?php
echo "\nRunning";
//database
require '/data/project/addbot/classes/database.php';
require '/data/project/addbot/config/database.php'; 
$db = new Database( $config['dbhost'], $config['dbport'], $config['dbuser'], $config['dbpass'], $config['dbname'], false);
unset($config['dbpass']);

$list = Database::mysql2array($db->doQuery('SELECT * from iwlinked_del ORDER BY added ASC LIMIT 100'));

echo "\nSelected ".count($list)." deletions\n";

//Remove the from the del table first
foreach ($list as $item)
{
$q = "DELETE FROM iwlinked_del WHERE (lang='".$db->mysqlEscape($item['lang'])."' AND article='".$db->mysqlEscape($item['article'])."')";
echo "\n$q";
$res = $db->doQuery($q);
if( !$res  ){echo "\n".$db->errorStr();}
}

//and then remove them from the main table
foreach ($list as $item)
{
$q = "DELETE FROM iwlinked WHERE (lang='".$db->mysqlEscape($item['lang'])."' AND article='".$db->mysqlEscape($item['article'])."')";
echo "\n$q";
$res = $db->doQuery($q);
if( !$res  ){echo "\n".$db->errorStr();}
}

?>
