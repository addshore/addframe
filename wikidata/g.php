<?

//Load
error_reporting(E_ERROR | E_WARNING | E_PARSE);
$options = getopt("",Array("lang::"));
echo "loading...";
sleep(1);

//Options
$config['General']['maxlag'] = "0";
$glang = $options['lang'];

//simple check to see if we are blocked
$br = file_get_contents("http://".$glang."wikipedia.org/w/api.php?action=query&list=blocks&bkusers=Addbot");
if(preg_match("/block id/",$br)){die("Blocked on wiki");} unset($br);

//Classes and configs
require '/data/project/addbot/classes/botclasses.php';
require '/data/project/addbot/classes/database.php';
require '/data/project/addbot/config/database.php';
require '/data/project/addbot/config/wiki.php';

//Initialise the wiki
$wiki = new wikipedia;
$wiki->url = "http://$glang.wikipedia.org/w/api.php";
global $wiki;
echo "\nLogging in...";
sleep(1);echo "..";
$wiki->login($config['user'],$config['password']);
unset($config['password']);

//Connect to the DB
echo "\nConnecting to database...";
$db = new Database( $config['dbhost'], $config['dbport'], $config['dbuser'], $config['dbpass'], $config['dbname'], false);
echo "done";

//Get a list from the DB and remove
$result = $db->select('iwlinked','*',"lang = '$glang' ORDER BY id ASC LIMIT 50");
$list = Database::mysql2array($result);
echo "\nGot ".count($list)." articles from $glang pending";
echo "\nRemoving";
$r = "DELETE FROM iwlinked WHERE";
$t = 0;
foreach ($list as $item){
	$t++;
	echo "r";
	$r .= " (`id`= '".$db->mysqlEscape($item['id'])."') OR";
	if($t >= 10)
	{
		$r = preg_replace('/ OR$/','',$r);//remove final OR
		$res = $db->doQuery($r);
		if( !$res  ){echo $db->errorStr();}
		$r = "DELETE FROM iwlinked WHERE";
		$t = 0;
		echo "R";
	}
}
if($t >= 1)
{
	$r = preg_replace('/ OR$/','',$r);//remove final OR
	$res = $db->doQuery($r);
	if( !$res  ){echo $db->errorStr();}
	echo "R";
}
unset ($r);

//For each item in the list
foreach ($list as $item)
{
	$name = $item['article'];
	$summary = "";
	echo ".";
	
	//Get the page and wikidata links
	$text = $wiki->getpage($name,null,true);
	if (strlen($text) < 1){echo "-";continue;}
		$wdlinks = $wiki->wikidatasitelinks($name,$glang);
		$counter = 0;
		$id = "";
		
		//If we have returned a result
		if(count($wdlinks) == 1)
		{
			//Use the 1 returned entity
			foreach($wdlinks as $ent)
			{
				$id = $ent['id'];
				//If it has a list of site links
				if(isset($ent['sitelinks']))
				{
					//Check each site link on our page
					foreach ($ent['sitelinks'] as $l)
					{
						//Format the language in the may it is used in IW links
						$lang = str_replace("_","-",str_replace("wiki","",$l['site']));
						//Create the regex matching the link we are looking for
						$link = "\n ?\[\[".preg_quote($lang,'/')." ?: ?".str_replace(" ","( |_)",preg_quote($l['title'],'/'))." ?\]\] ?";
						//If we can find said link
						if(preg_match('/'.$link.'/',$text))
						{
							//Remove it and increment the counter
							$text = preg_replace('/'.$link.'/',"",$text);
							$counter++;
						}
					}
				}
			}
			//If we have removed more than one links
			if($counter > 0)
			{
			//Set language specific options
			switch($glang) {


case "en":
 $summary = "[[User:Addbot|Bot:]] Migrating $counter interwiki links, now provided by [[Wikipedia:Wikidata|Wikidata]] on [[d:$id]]";
 $remove = "<!-- ?interwikis?( links?)? ?-->";
 break;
case "de":$summary = "Bot: Verschiebe $counter Interwikilinks, die nun in [[d:|Wikidata]] unter [[d:$id]] bereitgestellt werden"; break; 
case "fr":
 $summary = "Suis retirer $counter liens entre les wikis, actuellement fournis par [[d:|Wikidata]] sur la page [[d:$id]]";
 $remove = "<!-- ?((liens? )?inter(wiki|langue)s?|other languages) ?-->";
 break;
case "ab":$summary = ""; break; 
case "ace":$summary = ""; break; 
case "af":$summary = ""; break; 
case "ak":$summary = ""; break; 
case "als":$summary = ""; break; 
case "am":$summary = ""; break; 
case "an":$summary = ""; break; 
case "ang":$summary = ""; break; 
case "ar":$summary = "[[مستخدم:Addbot|بوت:]] ترحيل $counter وصلة إنترويكي, موجودة الآن في [[d:|ويكي بيانات]] على [[d:$id]]"; break;
case "arc":$summary = ""; break; 
case "arz":$summary = ""; break; 
case "as":$summary = ""; break; 
case "ast":$summary = ""; break; 
case "av":$summary = ""; break; 
case "ay":$summary = ""; break; 
case "az":$summary = ""; break; 
case "ba":$summary = ""; break; 
case "bar":$summary = ""; break; 
case "bat-smg":$summary = ""; break; 
case "bcl":$summary = ""; break; 
case "be":$summary = ""; break; 
case "be-x-old":$summary = ""; break; 
case "bg":$summary = ""; break; 
case "bh":$summary = ""; break; 
case "bi":$summary = ""; break; 
case "bjn":$summary = ""; break; 
case "bm":$summary = ""; break; 
case "bn":$summary = "[[ব্যবহারকারী:Addbot|বট]]: $counterটি আন্তঃউইকি সংযোগ সরিয়ে নেওয়া হয়েছে, যা এখন [[d:Wikidata:প্রধান পাতা|উইকিউপাত্ত]] এর [[d:$id]] এ রয়েছে"; break; 
case "bo":$summary = ""; break; 
case "bpy":$summary = ""; break; 
case "br":$summary = ""; break; 
case "bs":$summary = ""; break; 
case "bug":$summary = ""; break; 
case "bxr":$summary = ""; break; 
case "ca":$summary = "Bot: Traient $counter enllaços interwiki, ara proporcionats per [[d:|Wikidata]] a [[d:$id]]"; break; 
case "cbk-zam":$summary = ""; break; 
case "cdo":$summary = ""; break; 
case "ce":$summary = ""; break; 
case "ceb":$summary = ""; break; 
case "ch":$summary = ""; break; 
case "chr":$summary = ""; break; 
case "chy":$summary = ""; break; 
case "ckb":$summary = ""; break; 
case "co":$summary = ""; break; 
case "cr":$summary = ""; break; 
case "crh":$summary = ""; break; 
case "cs":$summary = "[[Wikipedista:Addbot|Bot:]] Odstranění $counter [[Wikipedie:Wikidata#Mezijazykové odkazy|odkazů interwiki]], které jsou nyní dostupné na [[d:|Wikidatech]] ([[d:$id]])"; break; 
case "csb":$summary = ""; break; 
case "cu":$summary = ""; break; 
case "cv":$summary = ""; break; 
case "cy":$summary = ""; break; 
case "da":$summary = "Bot: Migrerer $counter interwikilinks, som nu leveres af [[d:|Wikidata]] på [[d:$id]]"; break; 
case "diq":$summary = ""; break; 
case "dsb":$summary = ""; break; 
case "dv":$summary = ""; break; 
case "dz":$summary = ""; break; 
case "ee":$summary = ""; break; 
case "el":$summary = ""; break; 
case "eml":$summary = ""; break; 
case "en":$summary = ""; break; 
case "eo":$summary = "[[Uzanto:Addbot|Roboto:]] Forigo de $counter interlingvaj ligiloj, kiuj nun disponeblas per [[d:|Vikidatumoj]] ([[d:$id]])"; break; 
case "es":$summary = "Moviendo $counter enlaces interlingúisticos, ahora proporcionado(s) por [[d:|Wikidata]] en la página [[d:$id]]."; break; 
case "et":$summary = ""; break; 
case "eu":$summary = "[[User:Addbot|Robota:]] hizkuntza arteko $counter lotura lekualdatzen; aurrerantzean [[Wikipedia:Wikidata|Wikidata]] webgunean izango dira, [[d:$id]] orrian"; break;
case "ext":$summary = ""; break; 
case "fa":$summary = ""; break; 
case "ff":$summary = ""; break; 
case "fi":$summary = "Botti: poisti $counter [[d:|Wikidatan]] sivulle [[d:$id]] siirrettyä kielilinkkiä"; break;
case "fiu-vro":$summary = ""; break; 
case "fj":$summary = ""; break; 
case "fo":$summary = ""; break;  
case "frp":$summary = ""; break; 
case "frr":$summary = "[[User:Addbot|Bot:]] Fersküüw $counter interwiki-links, diar nü uun [[d:|Wikidata]] üüb det sidj [[d:$id]] paroot stun"; break; 
case "fur":$summary = ""; break; 
case "fy":$summary = ""; break; 
case "ga":$summary = ""; break; 
case "gag":$summary = ""; break; 
case "gan":$summary = ""; break; 
case "gd":$summary = ""; break; 
case "gl":$summary = "[[Usuario:Addbot|Bot:]] Retiro $counter ligazóns interlingüísticas, proporcionadas agora polo [[d:|Wikidata]] en [[d:$id]]"; break;
case "glk":$summary = ""; break; 
case "gn":$summary = ""; break; 
case "got":$summary = ""; break; 
case "gu":$summary = ""; break; 
case "gv":$summary = ""; break; 
case "ha":$summary = ""; break; 
case "hak":$summary = ""; break; 
case "haw":$summary = ""; break; 
case "he":$summary = "בוט: מעביר קישורי בינויקי ל[[ויקיפדיה:ויקינתונים|ויקינתונים]] - [[d:$id]]"; break;
case "hi":$summary = ""; break; 
case "hif":$summary = ""; break; 
case "hr":$summary = ""; break; 
case "hsb":$summary = ""; break; 
case "ht":$summary = ""; break; 
case "hu":$summary = "Bot: $counter interwiki link migrálva a [[d:|Wikidata]] [[d:$id]] adatába"; break; 
case "hy":$summary = ""; break; 
case "ia":$summary = ""; break; 
case "id":$summary = "[[Pengguna:Addbot|Bot:]] Migrasi $counter pranala interwiki, karena telah disediakan oleh [[Wikipedia:Wikidata|Wikidata]] pada item [[d:$id]]"; break; 
case "ie":$summary = ""; break; 
case "ig":$summary = ""; break; 
case "ik":$summary = ""; break; 
case "ilo":$summary = "[[Agar-aramat:Addbot|Bot:]] Agiyal-alis kadagiti $counter nga interwiki, a nait-iteden idiay [[Wikipedia:Wikidata|Wikidata]] iti [[d:$id]]"; break; 
case "io":$summary = ""; break; 
case "is":$summary = "Bot: Flyt $counter tungumálatengla, sem eru núna sóttir frá [[d:|Wikidata]] á [[d:$id]]"; break;
case "it":$summary = "migrazione di $counter interwiki links su [[d:Wikidata:Pagina_principale|Wikidata]] - [[d:$id]]"; break;
case "iu":$summary = ""; break; 
case "ja":$summary = "[[User:Addbot|ボット]]: 言語間リンク $counter 件を[[Wikipedia:ウィキデータ|ウィキデータ]]上の [[d:$id]] に転記"; break; 
case "jbo":$summary = ""; break; 
case "jv":$summary = ""; break; 
case "ka":$summary = ""; break; 
case "kaa":$summary = ""; break; 
case "kab":$summary = ""; break; 
case "kbd":$summary = ""; break; 
case "kg":$summary = ""; break; 
case "ki":$summary = ""; break; 
case "kk":$summary = ""; break; 
case "kl":$summary = ""; break; 
case "km":$summary = ""; break; 
case "kn":$summary = ""; break; 
case "ko":$summary = ""; break; 
case "koi":$summary = ""; break; 
case "krc":$summary = ""; break; 
case "ks":$summary = ""; break; 
case "ksh":$summary = ""; break; 
case "ku":$summary = ""; break; 
case "kv":$summary = ""; break; 
case "kw":$summary = ""; break; 
case "ky":$summary = ""; break; 
case "la":$summary = ""; break; 
case "lad":$summary = ""; break; 
case "lb":$summary = ""; break; 
case "lbe":$summary = ""; break; 
case "lez":$summary = ""; break; 
case "lg":$summary = ""; break; 
case "li":$summary = ""; break; 
case "lij":$summary = ""; break; 
case "lmo":$summary = ""; break; 
case "ln":$summary = ""; break; 
case "lo":$summary = ""; break; 
case "lt":$summary = ""; break; 
case "ltg":$summary = ""; break; 
case "lv":$summary = ""; break; 
case "map-bms":$summary = ""; break; 
case "mdf":$summary = ""; break; 
case "mg":$summary = ""; break; 
case "mhr":$summary = ""; break; 
case "mi":$summary = ""; break; 
case "min":$summary = "[[Pengguna:Addbot|Bot:]] Migrasi $counter pautan interwiki, dek lah disadioan jo [[Wikipedia:Wikidata|Wikidata]] pado [[d:$id]]"; break; 
case "mk":$summary = ""; break; 
case "ml":$summary = ""; break; 
case "mn":$summary = ""; break; 
case "mr":$summary = ""; break; 
case "mrj":$summary = ""; break; 
case "ms":$summary = ""; break; 
case "mt":$summary = ""; break; 
case "mwl":$summary = ""; break; 
case "my":$summary = ""; break; 
case "myv":$summary = ""; break; 
case "mzn":$summary = ""; break; 
case "na":$summary = ""; break; 
case "nah":$summary = ""; break; 
case "nap":$summary = ""; break; 
case "nds":$summary = ""; break; 
case "nds-nl":$summary = ""; break; 
case "ne":$summary = ""; break; 
case "new":$summary = ""; break; 
case "nl":$summary = "Verplaatsing van $counter interwikilinks die op [[d:|Wikidata]] beschikbaar zijn op [[d:$id]]"; break;
case "nn":$summary = "[[Brukar:Addbot|robot:]] fjernar $counter interwikilenkjer som er flytte til [[d:$id]] på [[Wikipedia:Wikidata|Wikidata]]"; break; 
case "no":$summary = "bot: Fjerner $counter interwikilenker som nå hentes fra [[d:$id]] på [[d:|Wikidata]]"; break;
case "nov":$summary = ""; break; 
case "nrm":$summary = ""; break; 
case "nso":$summary = ""; break; 
case "nv":$summary = ""; break; 
case "ny":$summary = ""; break; 
case "oc":$summary = ""; break; 
case "om":$summary = ""; break; 
case "or":$summary = ""; break; 
case "os":$summary = ""; break; 
case "pa":$summary = ""; break; 
case "pag":$summary = ""; break; 
case "pam":$summary = ""; break; 
case "pap":$summary = ""; break; 
case "pcd":$summary = ""; break; 
case "pdc":$summary = ""; break; 
case "pfl":$summary = ""; break; 
case "pi":$summary = ""; break; 
case "pih":$summary = ""; break; 
case "pl":$summary = ""; break; 
case "pms":$summary = ""; break; 
case "pnb":$summary = ""; break; 
case "pnt":$summary = ""; break; 
case "ps":$summary = ""; break; 
case "pt":$summary = ""; break; 
case "qu":$summary = ""; break; 
case "rm":$summary = ""; break; 
case "rmy":$summary = ""; break; 
case "rn":$summary = ""; break; 
case "ro":$summary = ""; break; 
case "roa-rup":$summary = ""; break; 
case "roa-tara":$summary = ""; break; 
case "ru":$summary = ""; break; 
case "rue":$summary = ""; break; 
case "rw":$summary = ""; break; 
case "sa":$summary = ""; break; 
case "sah":$summary = ""; break; 
case "sc":$summary = ""; break; 
case "scn":$summary = ""; break; 
case "sco":$summary = ""; break; 
case "sd":$summary = ""; break; 
case "se":$summary = ""; break; 
case "sg":$summary = ""; break; 
case "sh":$summary = ""; break; 
case "si":$summary = ""; break; 
case "simple":$summary = "Bot: $counter interwiki links moved, now provided by [[d:|Wikidata]] on [[d:$id]]"; break;
case "sk":$summary = "[[Redaktor:Addbot|Bot:]] Odstránenie $counter odkazov interwiki, ktoré sú teraz dostupné na [[d:|Wikiúdajoch]] ([[d:$id]])"; break; 
case "sl":$summary = "Bot: Migracija $counter interwikija/-ev, od zdaj gostuje(-jo) na [[d:|Wikipodatkih]], na [[d:$id]]"; break; 
case "sm":$summary = ""; break; 
case "sn":$summary = ""; break; 
case "so":$summary = ""; break; 
case "sq":$summary = ""; break; 
case "sr":$summary = ""; break; 
case "srn":$summary = ""; break; 
case "ss":$summary = ""; break; 
case "st":$summary = ""; break; 
case "stq":$summary = ""; break; 
case "su":$summary = ""; break; 
case "sv":$summary = "Bot överför $counter interwikilänk(ar), som nu återfinns på sidan [[d:$id]] på [[d:|Wikidata]]"; break;
case "sw":$summary = ""; break; 
case "szl":$summary = ""; break; 
case "ta":$summary = ""; break; 
case "te":$summary = ""; break; 
case "tet":$summary = "Bot: Hasai $counter ligasaun interwiki, ne'ebé agora mai husi [[d:$id]] iha [[d:|Wikidata]] "; break; 
case "tg":$summary = ""; break; 
case "th":$summary = ""; break; 
case "ti":$summary = ""; break; 
case "tk":$summary = ""; break; 
case "tl":$summary = ""; break; 
case "tn":$summary = ""; break; 
case "to":$summary = ""; break; 
case "tpi":$summary = ""; break; 
case "tr":$summary = ""; break; 
case "ts":$summary = ""; break; 
case "tt":$summary = ""; break; 
case "tum":$summary = ""; break; 
case "tw":$summary = ""; break; 
case "ty":$summary = ""; break; 
case "udm":$summary = ""; break; 
case "ug":$summary = ""; break; 
case "uk":$summary = ""; break; 
case "ur":$summary = "[[صارف:Addbot|ربالہ:]] منتقلی $counter بین الویکی روابط، اب [[d:|ویکی ڈیٹا]] میں [[d:$id]] پر موجود ہیں"; break;
case "uz":$summary = ""; break;
case "ve":$summary = ""; break; 
case "vec":$summary = ""; break; 
case "vep":$summary = ""; break; 
case "vi":$summary = "Bot: Di chuyển liên kết ngôn ngữ $counter đến [[d:|Wikidata]] tại [[d:$id]] [[M:User:Addbot/WDS|Addbot]]"; break; 
case "vls":$summary = ""; break; 
case "vo":$summary = ""; break; 
case "wa":$summary = ""; break; 
case "war":$summary = ""; break; 
case "wo":$summary = ""; break; 
case "wuu":$summary = ""; break; 
case "xal":$summary = ""; break; 
case "xh":$summary = ""; break; 
case "xmf":$summary = ""; break; 
case "yi":$summary = ""; break; 
case "yo":$summary = ""; break; 
case "za":$summary = ""; break; 
case "zea":$summary = ""; break; 
case "zh":$summary = "机器人：移除".$counter."个跨语言链接，现在由[[d:|维基数据]]的[[d:".$id."]]提供。"; break; 
case "zh-classical":$summary = ""; break; 
case "zh-min-nan":$summary = ""; break; 
case "zh-yue":$summary = ""; break; 
case "zu":$summary = ""; break; 
default:$summary = "[[M:User:Addbot|Bot:]] Migrating $counter interwiki links, now provided by [[d:|Wikidata]] on [[d:$id]] [[M:User:Addbot/WDS|(translate me)]]";


				}
			//If blank summary add it
			if($summary == "")
			{
				$summary = "[[M:User:Addbot|Bot:]] Migrating $counter interwiki links, now provided by [[d:|Wikidata]] on [[d:$id]] [[M:User:Addbot/WDS|(translate me)]]";
			}
			//Remove any comments we have been asked to
			if(isset($remove))
			{
				$text = preg_replace("/".$remove."/i","",$text);
				unset($remove);
			}
			
			//Remove any new lines left at the end of the article
			$text = preg_replace('/(\n\n)\n+$/',"$1", $text);
			$text = preg_replace('/^(\n|\r){0,5}$/',"", $text );
				
			}
		}			
	
	//If we are set to edit
	if($summary != "")
	{
		//edit
		echo "E";
		$wiki->edit($name,$text,$summary,true,true,null,true,$config['General']['maxlag']);
		sleep(10);
	}
	
	//Match any remaining links
	preg_match_all('/\n ?(\[\[(nostalgia|ten|test|aa|ab|ace|af|ak|als|am|an|ang|ar|arc|arz|as|ast|av|ay|az|ba|bar|bat-smg|bcl|be|be-x-old|bg|bh|bi|bjn|bm|bn|bo|bpy|br|bs|bug|bxr|ca|cbk-zam|cdo|ce|ceb|ch|cho|chr|chy|ckb|co|cr|crh|cs|csb|cu|cv|cy|da|de|diq|dsb|dv|dz|ee|el|eml|en|eo|es|et|eu|ext|fa|ff|fi|fiu-vro|fj|fo|fr|frp|frr|fur|fy|ga|gag|gan|gd|gl|glk|gn|got|gu|gv|ha|hak|haw|he|hi|hif|ho|hr|hsb|ht|hu|hy|hz|ia|id|ie|ig|ii|ik|ilo|io|is|it|iu|ja|jbo|jv|ka|kaa|kab|kbd|kg|ki|kj|kk|kl|km|kn|ko|koi|kr|krc|ks|ksh|ku|kv|kw|ky|la|lad|lb|lbe|lez|lg|li|lij|lmo|ln|lo|lt|ltg|lv|map-bms|mdf|mg|mh|mhr|mi|min|mk|ml|mn|mo|mr|mrj|ms|mt|mus|mwl|my|myv|mzn|na|nah|nap|nds|nds-nl|ne|new|ng|nl|nn|no|nov|nrm|nso|nv|ny|oc|om|or|os|pa|pag|pam|pap|pcd|pdc|pfl|pi|pih|pl|pms|pnb|pnt|ps|pt|qu|rm|rmy|rn|ro|roa-rup|roa-tara|ru|rue|rw|sa|sah|sc|scn|sco|sd|se|sg|sh|si|simple|sk|sl|sm|sn|so|sq|sr|srn|ss|st|stq|su|sv|sw|szl|ta|te|tet|tg|th|ti|tk|tl|tn|to|tpi|tr|ts|tt|tum|tw|ty|udm|ug|uk|ur|ve|vec|vep|vi|vls|vo|wa|war|wo|wuu|xal|xh|xmf|yi|yo|za|zea|zh|zh-classical|zh-min-nan|zh-yue|zu) ?: ?([^\]]+) ?\]\])/i',$text,$left);
	
	//if there are still links left over
	if(count($left[1]) > 0)
	{
		//Insert back into the DB with the number of linkes leftover
		$res = $db->doQuery("INSERT INTO iwlinked (lang, article, links) VALUES ('$glang', '$name', ".count($left[1]).")");
		if( !$res  ){echo $db->errorStr();}
		
	}
}

?>
