<?PHP
error_reporting(E_ALL);
ini_set('display_errors', '1');

require 'bot.login.php';
global $wiki;

$deadend = $wiki->categorymembers("Category:Dead-end_pages",true);

foreach($deadend as $page)
{

//$page = "User:Addshore/sandbox";

$isdeadend = true;
	//Make sure we are working in the mainspace
	if (preg_match('/:/',$page) == false)
	{
		echo "checking $page\n";
		//sleep(2);
		$text = $wiki->getpage($page);
		
		//Fix the template if it is under a section
		if(preg_match('/==.*?\{\{(Needs links|Dead( |-)?end( page)?|dep)(\| ?(date) ?(= ?(January|February|March|April|May|June|July|August|September|October|November|December) ?20[0-9][0-9])? ?){0,1}\}\}(\r\n|\n\n){0,2}/is',$text) != 0)
		{
			$text = preg_replace("/\{\{(Needs links|Dead( |-)?end( page)?|dep)/i","{{Dead end|section",$text);
			$wiki->edit($page,$text,"[[User:Addbot|Bot:]] Adding section parameter to Dead end tag ([[User_talk:Addbot|Report Errors]])",true);
			echo "Added section parameter";
			sleep(15);
			break;
		}
		
		//If it has the section parameter then also skip
		if(preg_match('/==.*?\{\{(Needs links|Dead( |-)?end( page)?|dep)\| ?section/is',$text)  == 0)
		{
			preg_match_all('/\[\[([a-z\/ _\(\)\|\.0-9]*)\]\]/i',$text, $links, PREG_PATTERN_ORDER);
			foreach($links[1] as $link)
			{
				if(preg_match('/\|/',$link) != 0)
				{
					$split = preg_split('/\|/',$link);
					$link = $split[0];
				}
			
				//do we still need to check / is it a valid link
				if ($isdeadend == true && preg_match('/:/',$link) == 0)
				{
					echo $link;
					if(strlen($wiki->getpage($link)) > 0)
					{
						echo "-- IS NOT A DEADEND per $link \n";
						//echo strlen($wiki->getpage($link))."\n";
						$isdeadend = false;
						sleep(5);
					}				
				}
			}
			
			//Has a link so remove tag
			if($isdeadend == false && preg_match('/\{\{(Needs links|Dead( |-)?end( page)?|dep) ?\| ?section/i',$text) == FALSE)
			{
				echo "NOT GOT A SECTION TAG";
				
				$text = preg_replace("/\{\{((Needs links|Dead( |-)?end( page)?|dep)(\| ?(date) ?(= ?(January|February|March|April|May|June|July|August|September|October|November|December) ?20[0-9][0-9])? ?){0,1} *\}\}(\r|\n){0,3})/i","",$text);
				
				/*
				$text = preg_replace("/(\{\{(Needs links|Dead( |-)?end( page)?|dep)(\| ?(date) ?=?((January|February|March|April|May|June|July|August|September|October|November|December) ?(20[0-9][0-9]))? ?){0,1} \}\}(\r\n|\n\n){0,3})/i","",$text);
				*/
				
				$text = preg_replace("/\|dead ?end ?= ?(January|February|March|April|May|June|July|August|September|October|November|December) ?20[0-9][0-9](\r|\n){0,3}/i","",$text);
				$text = preg_replace("/\{\{((Multiple|Article|Many)? ?issues|MI|multiple)\| ?(\r|\n){0,3}\}\}(\r|\n){0,3}/i","",$text);
				
				
				if($text != "")
				{
					$wiki->edit($page,$text,"[[User:Addbot|Bot:]] Removing Deadend Tag - Article has links ([[User_talk:Addbot|Report Errors]])",true);
					//sleep(15);
				}
			}
		}
	}
	
}

?>
