<?php
//database
require '/data/project/addbot/classes/database.php';
require '/data/project/addbot/config/database.php'; 
require '/data/project/addbot/classes/stathat.php';
require '/data/project/addbot/config/stathat.php';
$db = new Database( $config['dbhost'], $config['dbport'], $config['dbuser'], $config['dbpass'], $config['dbname'], false);
unset($config['dbpass']);
$c = 0;

while($c < 100)
{

	$c++;
	$q0 = "SELECT * FROM iwlink LIMIT 20";
	echo "$q0\n";
	$r = Database::mysql2array($db->doQuery($q0));
	sleep(1);
	foreach($r as $row)
	{
		$lang = $row['lang'];
		$article = $row['article'];
		$links = $row['links'];
		if($links == ""){$links = "NULL";}
		$q = "INSERT DELAYED INTO iwlinked (lang, article, links) VALUES ('$lang', '$article', '$links')";
		echo "$q\n";
		$r = $db->doQuery($q);
		if( !$r  )
		{
			echo $db->errorStr()."\n";
		}
		else
		{
			$q = "DELETE FROM iwlink WHERE ( lang='$lang' AND article='$article')";
			echo "$q\n";
			$r = $db->doQuery($q);
			if( !$r  ){echo $db->errorStr()."\n";}
		}
	}
	
}

?>
