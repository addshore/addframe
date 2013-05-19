<?php

error_reporting(E_ALL ^ E_NOTICE);

require '/data/project/addshore-dev/addwiki/classes/botclasses.php';

$wikidata      = new wikidata("http://www.wikidata.org/w/api.php");
global $wikidata;
echo "Logging in to wikidata\n";
$wikidata->login("user", "pass");

//Get the list of items
echo "Loading page\n";
$text = $wikidata->getpage("User:Byrial/Empty_items", null, true);
echo "Got page\n";
$lines = explode("\n",$text);
foreach ($lines as $line)
{
	
	if(substr($line,0,1) == "#")
	{
		preg_match_all('/\# \[\[Q([^\]]+)\]\]/', $line,$matches);
		
		//Skip until this one  //q10861802
		//if($matches[1][0] < 11176748){continue;}
		
		$id = "q".$matches[1][0];
		echo "\n$id ";
		
		$e = $wikidata->getentity($id);
		$e = $e['entities'][$id];
		$pageid = $e['pageid'];
		
		if(!isset($e['missing']))
		{
			//if there are no site links
			if(!isset($e['sitelinks']))
			{
				//print_r($e);
				echo "= ".count($e['sitelinks'])." sitelinks \033[0m\033[1;31m".count($e['aliases'])." aliases \033[0m\033[1;32m".count($e['labels'])." labels \033[1;34m".count($e['descriptions'])." descriptions \033[1;35m".count($e['claims'])." claims\033[0m\n\n";
				echo "\033[1;31m";if(isset($e['aliases'])){foreach($e['aliases'] as $a1){foreach($a1 as $a2){echo $a2['language'].":".$a2['value']."\n";}}}echo "\033[0m\n";
				echo "\033[1;32m";if(isset($e['labels'])){foreach($e['labels'] as $a1){echo $a1['language'].":".$a1['value']."\n";}}echo "\033[0m\n";
				echo "\033[1;34m";if(isset($e['descriptions'])){foreach($e['descriptions'] as $a1){echo $a1['language'].":".$a1['value']."\n";}}echo "\033[0m\n";
				echo "\033[1;35m";if(isset($e['claims'])){foreach($e['claims'] as $a1){echo $a1['language'].":".$a1['value']."\n";}}echo "\033[0m\n";
				
				echo "\033[1;33m";
				$lh = $wikidata->whatlinkshere($id);
				echo count($lh)." links to page\n";
				foreach($lh as $l){echo $l."\n";}
				echo "\n";echo "\033[0m\n";
				
				//continue if an item links here
				foreach($lh as $l){if(preg_match('/(Q|Property:P)[0-9]+/i', $l) >= 1){continue 2;}}
				
				$ph = $wikidata->pageidhistory($pageid);
				echo $wikidata->contribcount($ph[0]['user'])." edits by last user\n";
				foreach($ph as $r)
				{
					if($r['user'] == "Hazard-Bot"){$r['user'] = "\033[1;31m".$r['user']."\033[0m\n";}
					echo $r['size']." - ".$r['user']." - ".$r['comment']."\n";
				}
				echo "\n";
				
				if(count($e['sitelinks'])+count($e['aliases'])+count($e['labels'])+count($e['descriptions'])+count($e['claims']) == 0) echo "BIG ZERO >> ";
				
				
				$reason = "Does not meet required notability";
				if($ph[0]['user'] == "Hazard-Bot"){$wikidata->delete($id,$reason." - Emptied by Hazard-Bot");sleep(1);}
				else if($ph[0]['user'] == "GuySh"){$wikidata->delete($id,$reason." - Links removed to other entities");sleep(1);}
				else if($ph[0]['user'] == "Zakro"){$wikidata->delete($id,$reason." - Links removed to other entities by [[User:Zakro]]");sleep(1);}
				else if($ph[0]['user'] == "Pikne"){$wikidata->delete($id,$reason." - Links removed to other entities");sleep(1);}
				else if($ph[0]['user'] == "Calak"){$wikidata->delete($id,$reason." - Links removed to other entities");sleep(1);}
				else if($ph[0]['user'] == "Wylve"){$wikidata->delete($id,$reason." - Links removed to other entities by [[User:Wylve]]");sleep(1);}
				else if($ph[0]['user'] == "Succu"){$wikidata->delete($id,$reason." - Links removed to other entities by [[User:Succu]]");sleep(1);}
				else if($ph[0]['user'] == "Pirker"){$wikidata->delete($id,$reason." - Links removed to other entities by [[User:Pirker]]");sleep(1);}
				else if(preg_match('/^(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/ ',$ph[0]['user'])){continue;}
				else if(count($ph) == 2 AND $ph[1]['user'] == "Sk!dbot"){$wikidata->delete($id,$reason." - Links removed to other entities");sleep(1);}
				else if(count($ph) == 2 AND $ph[1]['user'] == "ElphiBot"){$wikidata->delete($id,$reason." - Links removed to other entities");}
				else if(count($ph) == 2 AND $ph[1]['user'] == "Legobot"){$wikidata->delete($id,$reason." - Links removed to other entities");sleep(1);}
				else if(count($ph) == 2 AND $ph[1]['user'] == "Makecat-bot"){$wikidata->delete($id,$reason." - Links removed to other entities");}
				else
				{
				
					$res = getInput("Do you want to delete this?");
					if( $res != "")
					{
						
						if($res == "c")
						{
							$reason = $reason." - Links moved, only Cat desc and label left";
						}
						else if($res == "h")
						{
							$reason = $reason." - Emptied by Hazard-Bot";
						}
						else if($res == "m")
						{
							$reason = $reason." - Links removed to other";
						}
						else if($res != "#")
						{
							$reason = $reason." - ".$res;
						}
						//delete it
						$wikidata->delete($id,$reason);
						echo "Deleted\n";
					}
				
				
				}
			}

		
		}
		//else
		//{
			echo "\n------------------------------------------------";
			echo "---------------------------------------------------------------------------------------------------------";
		//}
	}
}

function getInput($msg){
  fwrite(STDOUT, "$msg: ");
  $varin = trim(fgets(STDIN));
  return $varin;
}


?>