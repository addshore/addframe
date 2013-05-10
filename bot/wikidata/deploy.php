<?
//run everything in the site file
$file = __DIR__."sites.php";
$text = file_get_contents($file);
$split = explode("\n",$text);

echo shell_exec("qdel -u addshore");

sleep(5);

foreach ($split as $line)
{
	echo exec("echo 'cd /data/project/addbot/bot/wikidata/ && php g.php --lang=$line' | qsub -N wd.g.$line");
	sleep(1);
}
?>
