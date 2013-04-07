<?php
//database
require '/data/project/addbot/classes/database.php';
require '/data/project/addbot/config/database.php'; 
require '/data/project/addbot/classes/stathat.php';
require '/data/project/addbot/config/stathat.php';
$db = new Database( $config['dbhost'], $config['dbport'], $config['dbuser'], $config['dbpass'], $config['dbname'], false);
unset($config['dbpass']);

$c = 0;

$q = "SELECT MAX(id) as max_id, MIN(id) as min_id FROM iwlinked;";
echo "$q\n";
$r = Database::mysql2array($db->doQuery($q));
$min = $r[0]['min_id'];
$max = $r[0]['max_id'];
$rnd = mt_rand($min, $max);

while($c < 10000)
{
	$q2 = "INSERT INTO iwlink (lang, article, links) VALUES ";
	$q1 = "DELETE FROM iwlinked WHERE id IN (";

	$c++;
	$q0 = "SELECT * FROM iwlinked WHERE id<=$rnd ORDER BY id DESC LIMIT 20;";
	echo "$q0\n";
	$r = Database::mysql2array($db->doQuery($q0));
	foreach($r as $row)
	{
		$id = $row['id'];
		$lang = $row['lang'];
		$article = $row['article'];
		$links = $row['links'];
		if($links == ""){$links = "NULL";}
		$q1 .= "$id, ";
		$q2 .= "('$lang', '$article', $links), ";
	}
	$q1 = trim($q1,', ').");";
	$q2 = trim($q2,', ').";";
	echo "$q1\n";
	$r1 = $db->doQuery($q1);
	if( !$r1  ){echo $db->errorStr();}
	echo "$q2\n";
	$r2 = $db->doQuery($q2);
	if( !$r2  ){echo $db->errorStr();}
	
}

?>
