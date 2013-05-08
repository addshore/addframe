<?
error_reporting(E_ALL);
ini_set('display_errors', '1');

require 'bot.login.php';
global $wiki;

$pages = $wiki->getTransclusions("Template:plant-disease-stub",null);
$output = $wiki->getpage("User:Addshore/sandbox");

foreach( $pages as $page)
{
	$text = $wiki->getpage($page);
	$text = preg_replace("/(\{\{[^\}]*?\}\}|={1,6}[^=]*?={1,6}|\n\*{1,2} ?|\[https?[^\]]*?\]|\[\[(Category|Image|File|[a-z]{2,6}):[^\]]*?\]\]|\<references ?\/\>|<ref>.*?<\/ref>|<!--.*?-->)/is","",$text);
	$text = preg_replace("/\[\[[^\]]*?\]\]/","WORD",$text);//fill all links in with a single word
	$text = trim($text);
	if(str_word_count($text) > 500)
	{
		$output = $output."'''".$page." has ".str_word_count($text)." words'''\n";
	}
	else
	{
		$output = $output.$page." has ".str_word_count($text)." words\n";
	}
}

$wiki->edit("User:Addshore/sandbox",$output,"[[User:Addbot|Bot:]] Posting Stub Analysis",true);
	
?>