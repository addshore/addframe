<?PHP
require 'bot.login.php';

//Get the locations of templae
$pages = $wiki->getTransclusions("Template:Wikify",null,"&einamespace=0");

foreach($pages as $page)
{
	echo "Checking $page\n";
	$text = $wiki->getpage($page);
	$text = preg_replace("/(\r|\n){0,3}\{\{(wiki(fy(( |-)?section|ing)?)?|wk?fy?)[a-z\/ _=\(\)\|\.0-9]*\}\}(\r|\n){0,3}/i","",$text);
	if($text != "")
	{
		$wiki->edit($page,$text,"[[User:Addbot|Bot:]] Removing phased out template",true);
		//sleep(15);
	}
}

?>