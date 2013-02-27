<?

$options = getopt("",Array("lang::","num::"));

echo "loading...";
sleep(1);

// load the classes and stuff
require 'classes/botclasses.php';
require '/home/addshore/.password.addbot';                  // $config['password'] = 'password';

$glang = $options['lang'];

error_reporting(E_ERROR | E_WARNING | E_PARSE);

// initialise the wiki
$wiki = new wikipedia;
$wiki->url = "http://$glang.wikipedia.org/w/api.php";
global $wiki;

echo "\nLogging in...";
sleep(1);echo "..";

// perform the login
$wiki->login("Addbot",$config['password']);
unset($config['password']);
echo "done";

//Get further config stuff
$config['General']['maxlag'] = "0";

$runn = $options['num'];

$filename = "/data/project/addbot/enwiki/$glang/$glang ".$runn.".txt";

//TODO LOAD LIST
$list = file_get_contents($filename);
$list = explode("\n",$list);

//$oc = file_get_contents("/data/project/addbot/enwiki/$glang/count.txt");
//if ($oc == ""){$oc = 0;}else{$oc = intval($oc);}
//$c = 0;

foreach ($list as $name)
{
	//$c = $c + 1;
	//if ($c < $oc){echo ",";continue;}
	//file_put_contents("/data/project/addbot/enwiki/$glang/count.txt",$c);
	$summary = "";
	//Check the article
	//echo "\nChecking ".$name;
	echo ".";
	$text = $wiki->getpage($name);
	if (strlen($text) < 2){echo "-";continue;}
	
	$result = preg_match("/^((Talk|User|Wikipedia|File|Image|Mediawiki|Template|Help|Category|Portal|Book|Education( |_)program|TimedText)(( |_)talk)?):?/i",$name,$matches);
	if($result == 0)
	{
			//echo "\n> Is Article";

				//get the other links
				$r = $wiki->wikidatasitelinks($name,$glang);
				$counter = 0;
				$id = "";
				
				//if there is only 1 entity (i.e. the wikidata stuff isnt broken somewhere)
				if(count($r) == 1)
				{
					//foreach entitiy found
					foreach($r as $ent)
					{
						$id = $ent['id'];
						//Check if we have site links
						if(isset($ent['sitelinks']))
						{
							//for each sitelink in the entity
							foreach ($ent['sitelinks'] as $l)
							{
								$lang = str_replace("_","-",str_replace("wiki","",$l['site']));
								$link = "\n[[".$lang.":".$l['title']."]]";
								if(preg_match('/'.preg_quote($link,'/').'/',$text))
								{
									$text = str_replace($link,"",$text);//remove the link
									$counter++;
								}
							}
						}
					}
					if($counter > 0)
					{
						//$summary = "Bot: Migrating $counter interwiki links, now provided by [[Wikipedia:Wikidata|Wikidata]] on [[d:$id]]";
						$summary = "בוט: מעביר קישורי בינויקי ל[[ויקיפדיה:ויקינתונים|ויקינתונים]] - [[d:$id]]";
					}
				}			
				
			$text = preg_replace('/(\n\n)\n+/',"$1", $text);
			$text = preg_replace('/^(\n|\r){0,5}/',"", $text );
	}
	
	//If we have a sig change then we want to post
	if($summary != "")
	{
		//Then we can post
		//echo "\n> POST: ".$summary;
		echo "E";
		$wiki->edit($name,$text,$summary,true,true,null,false,"0");
	}
	
}

?>
