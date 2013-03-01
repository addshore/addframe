<?

$options = getopt("",Array("lang::","file::"));

echo "loading...";
sleep(1);

// load the classes and stuff
require '/data/project/addbot/classes/botclasses.php';
require '/data/project/addbot/classes/database.php';

// database settings
$config['dbhost'] = 'i-000000b4.pmtpa.wmflabs';
$config['dbport'] = '3306';
$config['dbuser'] = 'addshore';
require '/home/addshore/.password.db';            //$config['dbpass'] = 'password';
$config['dbname'] = 'addbot';
require '/home/addshore/.password.addbot';        //$config['password'] = 'password';

$config['General']['maxlag'] = "0";
$glang = $options['lang'];
$file = $options['file'];

error_reporting(E_ERROR | E_WARNING | E_PARSE);

// initialise the wiki
$wiki = new wikipedia;
$wiki->url = "http://$glang.wikipedia.org/w/api.php";
global $wiki;

echo "\nLogging in...";
sleep(1);echo "..";
$wiki->login("Addbot",$config['password']);
unset($config['password']);

if(!isset($file))
{
	echo "\nConnecting to database...";
	$db = new Database( $config['dbhost'], $config['dbport'], $config['dbuser'], $config['dbpass'], $config['dbname'], false);
	echo "done";

	$result = $db->select('iwlinked','*',"lang = '$glang'",array("LIMIT" => 200));
	$list = Database::mysql2array($result);
	echo "\nGot ".count($list)." articles from $glang pending";
	echo "\nRemoving";
	foreach ($list as $item){
		$res = $db->delete('iwlinked',array('article' => $item['article'],'lang' => $glang));
		if( !$res  ){echo $db->errorStr();}
		echo "r";
	}
}
else
{
	//Get and split a file
	echo "\nGetting from $file ...";
	$list = file_get_contents($file);
	$list = explode("\n",$list);
	echo "\nGot ".count($list)." articles from $file";
}

foreach ($list as $item)
{
	if(isset($item['article']))
	{$name = $item['article'];}
	else{$name = $item;}
	
	$summary = "";
	echo ".";
	$text = $wiki->getpage($name);
	if (strlen($text) < 2){echo "-";continue;}
	
	//$result = preg_match("/^((Talk|User|Wikipedia|File|Image|Mediawiki|Template|Help|Category|Portal|Book|Education( |_)program|TimedText)(( |_)talk)?):?/i",$name,$matches);
	//if($result == 0)
	//{
				$r = $wiki->wikidatasitelinks($name,$glang);
				$counter = 0;
				$id = "";
				
				if(count($r) == 1)
				{
					foreach($r as $ent)
					{
						$id = $ent['id'];
						if(isset($ent['sitelinks']))
						{
							foreach ($ent['sitelinks'] as $l)
							{
								$lang = str_replace("_","-",str_replace("wiki","",$l['site']));
								$link = "\n[[".$lang.":".$l['title']."]]";
								$link = str_replace(" ","( |_)",preg_quote($link,'/'));
								if(preg_match('/'.$link.'/',$text))
								{
									$text = preg_replace('/'.$link.'/',"",$text);//remove the link
									$counter++;
								}
							}
						}
					}
					if($counter > 0)
					{
					switch($glang) {
						case "en":$summary = "[[User:Addbot|Bot:]] Migrating $counter interwiki links, now provided by [[Wikipedia:Wikidata|Wikidata]] on [[d:$id]]"; break;
						case "it":$summary = "migrazione di $counter interwiki links su [[d:Wikidata:Pagina_principale|Wikidata]] - [[d:$id]]"; break;
						case "he":$summary = "בוט: מעביר קישורי בינויקי ל[[ויקיפדיה:ויקינתונים|ויקינתונים]] - [[d:$id]]"; break;
					}
						
					}
				}			
				
			$text = preg_replace("/<!-- ?interwikis?( links?)? ?-->/i","",$text);
				
			$text = preg_replace('/(\n\n)\n+/',"$1", $text);
			$text = preg_replace('/^(\n|\r){0,5}/',"", $text );
	//}
	
	if($summary != "")
	{
		echo "E";
		$wiki->edit($name,$text,$summary,true,true,null,false,"0");
	}
	
}

?>
