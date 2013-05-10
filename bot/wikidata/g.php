<?
//exit();
$start_time = MICROTIME(TRUE);
//Load
//error_reporting(E_ERROR | E_PARSE);
$options = getopt("", Array(
	"lang::"
));
echo "loading...";
//Options
$config['General']['maxlag'] = "2";
$glang                       = $options['lang'];
$apilang                     = str_replace('-', '', $glang);

//Classes and configs
require __DIR__.'/../../classes/botclasses.php';
require __DIR__.'/../../classes/database.php';
require __DIR__.'/../../classes/stathat.php';
require __DIR__.'/../../config/stathat.cfg';
require __DIR__.'/../../config/database.cfg';
require __DIR__.'/../../config/wiki.cfg';
require __DIR__.'/namespace.php';

//Initialise the wiki array
$wiki           = Array();
//and wikidata
$wikidata      = new wikidata("http://www.wikidata.org/w/api.php");
global $wikidata;
echo "\nLogging in to wikidata.org...";
$wikidata->login($config['user'], $config['password']);

//TODO run checkings from a WD page

//Connect to the DB
echo "\nConnecting to database...";
$db = new Database($config['dbhost'], $config['dbport'], $config['dbuser'], $config['dbpass'], $config['dbname'], false);
echo "done";

$dblang = str_replace("-","_",$glang);
//Get a list from the DB and remove
$result = $db->select('iwlink', '*', "lang='$dblang' ORDER BY checked ASC"); //LIMIT " . $toget);
$list   = Database::mysql2array($result);
echo "\nGot " . count($list) . " articles from $dblang pending";

//For each item in the list
foreach ($list as $item) {

	$STUFFTOADD = false;
	$CONFLICT = false;

	$title1 = $item['article'];
	//$title1 = 'Accountancy';
	$lang1 = $item['lang'];
	
	checklogin($glang);
	$text = $wiki[$glang]->getpage($title1, null, true);
	echo "\n\nGot [[\033[32m$lang1:$title1\033[0m]]";
	stathat_ez_count($config['stathatkey'], "Addbot - IW Removal - Articles Loaded", 1);
	if(strlen($text) < 1){continue;}
	
	$id = "";
	/*
	//check the links found in the text
	$toaddtowikidata = Array();
	$linksonpage1 = getlinksfromtext($text);
	foreach($linksonpage1 as $langt => $titlet)
	{
		$return = $wikidata->wikidatasitelinks($titlet,str_replace("-","_",$langt)."wiki");
		foreach ($return as $q)
		{
			if(!isset($q['id']))
			{
				//only add them if they are not on WD
				$toaddtowikidata[$langt] = $titlet;
				$STUFFTOADD = true;
			}
			else
			{
				$CONFLICT = true;
			}
		}
	}

	//try and get the id for our current article
	$return = getlinksfromwikidata($lang1,$title1);
	if(isset($return['id']))
	{
		$id = $return['id'];
		foreach($toaddtowikidata as $langt => $titlet)
		{
			if(isset($return[$langt]))
			{
				//make sure there are no conflicts
				unset($toaddtowikidata[$langt]);
			}
		}
	}
	unset($return);
	
	//if we have something to add to WD
	if($STUFFTOADD && $CONFLICT == false)
	{
		$extra = "";
		$result = "";
		if($id != "")
		{
			$result = $wikidata->editentity(composeentity($toaddtowikidata),$id);
			$extra = "old";
		}
		else
		{
			$toaddtowikidata[$lang1] = $title1;
			$result = $wikidata->editentity(composeentity($toaddtowikidata));
			$extra = "new";
		}
		if($result['success'] == 1)
		{
		echo "\n\033[1;35m\n".count($linksonpage1)." links added to wikidata ($extra)\033[0m";stathat_ez_count($config['stathatkey'], "Addbot - Wikidata Edits", 1);
		}else{echo "\n\033[1;35m\nFAILED to add links to wikidata ($extra)\033[0m";}
	}
	
	*/
	
	//now try and remove stuff from text
	$return = getlinksfromwikidata($lang1,$title1);
	$result = removelinksfromtext($text,$return['links']);
	if($text != $result['text'])//if it is different
	{
		//skip certain namespaces
		if (!preg_match('/^('.implode($ns[$glang][1]).'|'.implode($ns[$glang][2]).'|'.implode($ns[$glang][3]).'|'.implode($ns[$glang][-1]).'|'.implode($ns[$glang][8]).'|'.implode($ns[$glang][9]).'):/i',$title1))
		{
			$eresult = $wiki[$glang]->edit($title1, $result['text'], getmysum($glang, $return['id'], $result['removed']), true, true, null, true, $config['General']['maxlag']);
			echo "\n\033[34mEDIT: Removed ".$result['removed']." links \033[0m";
			stathat_ez_count($config['stathatkey'], "Addbot - IW Removal - Global Removals", $result['removed']);
			stathat_ez_count($config['stathatkey'], "Addbot - IW Removal - Global Edits", 1);
		}
	}
	
	//update db accordingly
	$linksonpage2 = getlinksfromtext($wiki[$glang]->getpage($title1, null, true));
	if(count($linksonpage2) == 0)
	{
		$res = $db->doQuery("DELETE FROM iwlink WHERE lang='".$db->mysqlEscape($dblang)."' and article='".$db->mysqlEscape($title1)."'");
		if (!$res) {echo $db->errorStr();}
		else
		{
			echo "\n\033[1;31mRemoved from database $lang1:$title1 (" . count($linksonpage2) . " links left)\033[0m";
			stathat_ez_count($config['stathatkey'], "Addbot - IW Removal - DB Removal", 1);
		}
	}
	else
	{
		$res = $db->doQuery("UPDATE iwlink SET links='".strval(count($linksonpage2))."' WHERE lang='".$db->mysqlEscape($dblang)."' and article='".$db->mysqlEscape($title1)."'");
		if (!$res) {echo $db->errorStr();}
		echo "\n\033[1;31mUpdated in database $lang1:$title1 (" . count($linksonpage2) . " links left)\033[0m";
	}
}


function composeentity($langlinks = null,$labels = null)
{
	$return = Array();
	if($langlinks != null)
	{
		foreach($langlinks as $lang => $title)
		{
			$wdlang = str_replace('-','_',$lang).'wiki';
			$return['sitelinks'][$wdlang]['site'] = $wdlang;
			$return['sitelinks'][$wdlang]['title'] = $title;
			$return['labels'][$lang]['language'] = $lang;
			$return['labels'][$lang]['value'] = $title;
		}
	}
	//if we have specific labels overwrite any we set from titles
	if($labels != null)
	{
		foreach($labels as $lang =>$label)
		{
			$return['labels']['$lang']['language'] = $lang;
			$return['labels']['$lang']['value'] = $label;
		}
	}
	return $return;
}

function checklogin($lang)
{
	global $wiki,$config;
	if (!isset( $wiki[$lang] ) )
	{
		$wiki[$lang] = new wikipedia("http://$lang.wikipedia.org/w/api.php");
		echo "\nLogging in to $lang.wikipedia.org...";
		$wiki[$lang]->login($config['user'], $config['password']);
	}
}

function getlinksfromtext($text)
{

	global $ns;
	$return = Array();
	preg_match_all("/\[\[(nostalgia|ten|aa|ab|ace|af|ak|als|am|an|ang|ar|arc|arz|as|ast|av|ay|az|ba|bar|bat-smg|bcl|be|be-x-old|bg|bh|bi|bjn|bm|bn|bo|bpy|br|bs|bug|bxr|ca|cbk-zam|cdo|ce|ceb|ch|cho|chr|chy|ckb|co|cr|crh|cs|csb|cu|cv|cy|da|de|diq|dsb|dv|dz|ee|el|eml|en|eo|es|et|eu|ext|fa|ff|fi|fiu-vro|fj|fo|fr|frp|frr|fur|fy|ga|gag|gan|gd|gl|glk|gn|got|gu|gv|ha|hak|haw|he|hi|hif|ho|hr|hsb|ht|hu|hy|hz|ia|id|ie|ig|ii|ik|ilo|io|is|it|iu|ja|jbo|jv|ka|kaa|kab|kbd|kg|ki|kj|kk|kl|km|kn|ko|koi|kr|krc|ks|ksh|ku|kv|kw|ky|la|lad|lb|lbe|lez|lg|li|lij|lmo|ln|lo|lt|ltg|lv|map-bms|mdf|mg|mh|mhr|mi|min|mk|ml|mn|mo|mr|mrj|ms|mt|mus|mwl|my|myv|mzn|na|nah|nap|nds|nds-nl|ne|new|ng|nl|nn|no|nov|nrm|nso|nv|ny|oc|om|or|os|pa|pag|pam|pap|pcd|pdc|pfl|pi|pih|pl|pms|pnb|pnt|ps|pt|qu|rm|rmy|rn|ro|roa-rup|roa-tara|ru|rue|rw|sa|sah|sc|scn|sco|sd|se|sg|sh|si|simple|sk|sl|sm|sn|so|sq|sr|srn|ss|st|stq|su|sv|sw|szl|ta|te|tet|tg|th|ti|tk|tl|tn|to|tpi|tr|ts|tt|tum|tw|ty|udm|ug|uk|ur|ve|vec|vep|vi|vls|vo|wa|war|wo|wuu|xal|xh|xmf|yi|yo|za|zea|zh|zh-classical|zh-min-nan|zh-yue|zu):([^\]]+)\]\]/i",$text,$match);
	//[0] is whole match [1] is langs [2] is articles
	foreach($match[0] as $key => $m)
	{
		//only return if not talk, user, or usertalk
		if (!preg_match('/^('.implode($ns[$match[1][$key]][1]).'|'.implode($ns[$match[1][$key]][2]).'|'.implode($ns[$match[1][$key]][3]).'|'.implode($ns[$match[1][$key]][-1]).'|'.implode($ns[$match[1][$key]][8]).'|'.implode($ns[$match[1][$key]][9]).'):/i',$match[2][$key]))
		{
			//only return if not # (a section)
			if(!strstr($match[2][$key],'#'))
			{
				$return[$match[1][$key]] = $match[2][$key];
			}
		}
	}
	return $return;
}

function getlinksfromwikidata($lang,$title)
{
	global $wikidata;
	$return = Array();
	$wdlinks = $wikidata->wikidatasitelinks($title,str_replace("-","_",$lang)."wiki");
	$id = "Q";
	if(count($wdlinks) > 0)
	{
		
		foreach ($wdlinks as $entity)
		{
			$id = "";
			if(isset($entity['id']))
			{
				$id = $entity['id'];
			}
			if(isset($entity['sitelinks']))
			{
				foreach($entity['sitelinks'] as $sitelink)
				{
					$linklang = str_replace("_", "-", str_replace("wiki", "", $sitelink['site']));
					$return[$linklang] = $sitelink['title'];
				}
			}
		}
	}
	return Array('links'=>$return,'id'=>$id);
}

function removelinksfromtext($text,$links)
{
	global $ns;
	$return = "";
	$return = $text;
	$counter = 0;
	if(count($links) > 0)
	{
		foreach($links as $lang => $title)
		{
			//remove lags that are the same
			if ($lang == "no") {
				$lang = "(nb|no)";
			} else if ($lang == "zh-min-nan") {
				$lang = "(zh-min-nan|nan)";
			}
			
			//Create the regex matching the link we are looking for
			$link = "\n ?\[\[".$lang." ?: ?".str_replace(" ","( |_)",preg_quote($title,'/'))." ?\]\] ?";
			//If we can find said link
			if(preg_match('/'.$link.'/',$return))
			{
				//Remove it and increment the counter
				$return = preg_replace('/'.$link.'/i',"",$return);
				$counter++;
			}
		}
	}
	$return = preg_replace('/(\n\n)\n+$/', "$1", $return);
	$return = preg_replace('/^(\n|\r){0,5}$/', "", $return);
	
	return Array('text'=>$return,'removed'=>$counter);
}




function getmysum($lang, $id, $counter = 1)
{
	$summary = "";
	//Set language specific options
	switch ($lang) {
		case "en":
			$summary = "[[User:Addbot|Bot:]] Migrating $counter interwiki links, now provided by [[Wikipedia:Wikidata|Wikidata]] on [[d:$id]]";
			break;
		case "de":
			$summary = "Bot: $counter [[Hilfe:Internationalisierung|Interwiki-Link(s)]] nach [[WP:Wikidata|Wikidata]] ([[d:$id]]) migriert";
			break;
		case "fr":
			$summary = "Retrait de $counter liens interlangues, désormais fournis par [[d:|Wikidata]] sur la page [[d:$id]]";
			break;
		case "ab":
			$summary = "";
			break;
		case "ace":
			$summary = "";
			break;
		case "af":
			$summary = "Verplasing van $counter interwikiskakels wat op [[d:|Wikidata]] beskikbaar is op [[d:$id]]";
			break;
		case "ak":
			$summary = "";
			break;
		case "als":
			$summary = "$counter Link zu anderne Sprooche wäg tue, die sind jetzt uf [[d:|Wikidata]] bi [[d:$id]] z finde"; break; 
			break;
		case "am":
			$summary = "[[User:Addbot|ሎሌ፦]] መያያዣዎች ወደ $counter ልሳናት አሁን በ[[Wikipedia:Wikidata|Wikidata]] ገጽ [[d:$id]] ስላሉ ተዛውረዋል።";
			break;
		case "an":
			$summary = "";
			break;
		case "ang":
			$summary = "";
			break;
		case "ar":
			$summary = "[[مستخدم:Addbot|بوت:]] ترحيل $counter وصلة إنترويكي, موجودة الآن في [[d:|ويكي بيانات]] على [[d:$id]]";
			break;
		case "arc":
			$summary = "";
			break;
		case "arz":
			$summary = "";
			break;
		case "as":
			$summary = "";
			break;
		case "ast":
			$summary = "Moviendo $counter enllace(s) interllingüístico(s), agora proporcionao(s) por [[d:|Wikidata]] na páxina [[d:$id]]";
			break;
		case "av":
			$summary = "";
			break;
		case "ay":
			$summary = "";
			break;
		case "az":
			$summary = "";
			break;
		case "ba":
			$summary = "";
			break;
		case "bar":
			$summary = "$counter Links zu åndere Språchn weggatån, de wås jetz auf [[d:|Wikidata]] bei [[d:$id]] zam Findn san";
			break;
		case "bat_smg":
			$summary = "Perkeliamas $counter tarpkalbėnės nūruodas, daba esontės [[d:|Wikidata]] poslapī [[d:$id]]";
			break;
		case "bcl":
			$summary = "";
			break;
		case "be":
			$summary = "Робат перанёс $counter міжмоўных спасылак да аб'екта [[d:$id]] на [[:en:Wikipedia:Wikidata|Wikidata]]";
			break;
		case "be_x_old":
			$summary = "[[User:Addbot|Робат]]: перанос $counter міжмоўных спасылак у [[Вікіпэдыя:Вікізьвесткі|Вікізьвесткі]] да аб’екта [[d:$id]]";
			break;
		case "be_tarask":
			$summary = "[[User:Addbot|Робат]]: перанос $counter міжмоўных спасылак у [[Вікіпэдыя:Вікізьвесткі|Вікізьвесткі]] да аб’екта [[d:$id]]";
			break;
		case "bg":
			$summary = "[[Потребител:Addbot|Робот]]: Преместване на $counter междуезикови препратки към [[:en:Wikipedia:Wikidata|Уикиданни]], в [[d:$id]]";
			break;
		case "bh":
			$summary = "";
			break;
		case "bi":
			$summary = "";
			break;
		case "bjn":
			$summary = "";
			break;
		case "bm":
			$summary = "";
			break;
		case "bn":
			$summary = "[[ব্যবহারকারী:Addbot|বট]]: $counter টি আন্তঃউইকি সংযোগ স্থানান্তর করেছে, যা এখন [[d:|উইকিউপাত্তের]] - [[d:$id]] এ রয়েছে";
			break;
		case "bo":
			$summary = "";
			break;
		case "bpy":
			$summary = "";
			break;
		case "br":
			$summary = "";
			break;
		case "bs":
			$summary = "[[Korisnik:Addbot|Bot:]] premještanje $counter međuwiki linkova koji su sada dostupni na stranici [[d:$id]] na [[d:|Wikidati]]";
			break;
		case "bug":
			$summary = "";
			break;
		case "bxr":
			$summary = "";
			break;
		case "ca":
			$summary = "Bot: Traient $counter enllaços interwiki, ara proporcionats per [[d:|Wikidata]] a [[d:$id]]";
			break;
		case "cbk_zam":
			$summary = "";
			break;
		case "cdo":
			$summary = "";
			break;
		case "ce":
			$summary = "";
			break;
		case "ceb":
			$summary = "";
			break;
		case "ch":
			$summary = "";
			break;
		case "chr":
			$summary = "";
			break;
		case "chy":
			$summary = "";
			break;
		case "ckb":
			$summary = "[[بەکارھێنەر:Addbot|بۆت:]] گواستنەوەی $counter بەستەری نێوانویکی، ئێستا دابین کراوە لەسەر [[d:| ویکیدراوە]] لە [[d:$id]]";
			break;
		case "co":
			$summary = "";
			break;
		case "cr":
			$summary = "";
			break;
		case "crh":
			$summary = "";
			break;
		case "cs":
			$summary = "[[Wikipedista:Addbot|Bot:]] Odstranění $counter [[Wikipedie:Wikidata#Mezijazykové odkazy|odkazů interwiki]], které jsou nyní dostupné na [[d:|Wikidatech]] ([[d:$id]])";
			break;
		case "csb":
			$summary = "";
			break;
		case "cu":
			$summary = "";
			break;
		case "cv":
			$summary = "";
			break;
		case "cy":
			$summary = "$counter cysylltiadau rhyngwici a ddarperir bellach gan Wikidata ynn [[d:$id]]";
			break;
		case "da":
			$summary = "Bot: Migrerer $counter interwikilinks, som nu leveres af [[d:|Wikidata]] på [[d:$id]]";
			break;
		case "diq":
			$summary = "";
			break;
		case "dsb":
			$summary = "";
			break;
		case "dv":
			$summary = "";
			break;
		case "dz":
			$summary = "";
			break;
		case "ee":
			$summary = "";
			break;
		case "el":
			$summary = "[[User:Addbot|Ρομπότ:]] Μεταφέρω $counter σύνδεσμους interwiki, που τώρα παρέχονται από τα [[Wikipedia:Wikidata|Wikidata]] στο [[d:$id]]";
			break;
		case "eml":
			$summary = "";
			break;
		case "en":
			$summary = "";
			break;
		case "eo":
			$summary = "[[Uzanto:Addbot|Roboto:]] Forigo de $counter interlingvaj ligiloj, kiuj nun disponeblas per [[d:|Vikidatumoj]] ([[d:$id]])";
			break;
		case "es":
			$summary = "Moviendo $counter enlace(s) interlingüístico(s), ahora proporcionado(s) por [[d:|Wikidata]] en la página [[d:$id]]";
			break;
		case "et":
			$summary = "[[User:Addbot|Robot]]: muudetud $counter intervikilink(i), mis on nüüd andmekogus [[d:$id|Wikidata]]";
			break;
		case "eu":
			$summary = "[[User:Addbot|Robota:]] hizkuntza arteko $counter lotura lekualdatzen; aurrerantzean [[Wikipedia:Wikidata|Wikidata]] webgunean izango dira, [[d:$id]] orrian";
			break;
		case "ext":
			$summary = "";
			break;
		case "fa":
			$summary = "[[کاربر:Addbot|ربات:]] انتقال $counter پیوند میان‌ویکی به [[d:$id]] در [[ویکی‌پدیا:ویکی‌داده|ویکی‌داده]]";
			break;
		case "ff":
			$summary = "";
			break;
		case "fi":
			$summary = "[[Käyttäjä:Addbot|Botti]] poisti $counter [[Wikipedia:Wikidata|Wikidatan]] sivulle [[d:$id]] siirrettyä kielilinkkiä";
			break;
		case "fiu_vro":
			$summary = "";
			break;
		case "fj":
			$summary = "";
			break;
		case "fo":
			$summary = "";
			break;
		case "frp":
			$summary = "";
			break;
		case "frr":
			$summary = "[[User:Addbot|Bot:]] Fersküüw $counter interwiki-links, diar nü uun [[d:|Wikidata]] üüb det sidj [[d:$id]] paroot stun";
			break;
		case "fur":
			$summary = "";
			break;
		case "fy":
			$summary = "[[Gebruiker:Addbot|Robot:]] Verplaatsing van $counter interwikilinks. Deze staan nu op [[d:|Wikidata]] onder [[d:$id]]";
			break;
		case "ga":
			$summary = "";
			break;
		case "gag":
			$summary = "";
			break;
		case "gan":
			$summary = "";
			break;
		case "gd":
			$summary = "";
			break;
		case "gl":
			$summary = "[[Usuario:Addbot|Bot:]] Retiro $counter ligazóns interlingüísticas, proporcionadas agora polo [[d:|Wikidata]] en [[d:$id]]";
			break;
		case "glk":
			$summary = "";
			break;
		case "gn":
			$summary = "";
			break;
		case "got":
			$summary = "";
			break;
		case "gu":
			$summary = "[[User:Addbot|બોટ:]] $counter આંતરવિકિ કડીઓ સ્થળાંતર કરી, હવે તે [[Wikipedia:Wikidata|વિકિડેટા]] ખાતે [[d:$id]] પરથી મળશે";
			break;
		case "gv":
			$summary = "";
			break;
		case "ha":
			$summary = "";
			break;
		case "hak":
			$summary = "";
			break;
		case "haw":
			$summary = "";
			break;
		case "he":
			$summary = "בוט: מעביר קישורי בינויקי ל[[ויקיפדיה:ויקינתונים|ויקינתונים]] - [[d:$id]]";
			break;
		case "hi":
			$summary = "";
			break;
		case "hif":
			$summary = "";
			break;
		case "hr":
			$summary = "[[Suradnik:Addbot|Bot:]] brisanje $counter međuwiki poveznica premještenih u stranicu [[d:$id]] na [[d:|Wikidati]]";
			break;
		case "hsb":
			$summary = "";
			break;
		case "ht":
			$summary = "";
			break;
		case "hu":
			$summary = "Bot: $counter interwiki link áthelyezve a [[d:|Wikidata]] [[d:$id]] adatába";
			break;
		case "hy":
			$summary = "[[Մասնակից:Addbot|Բտթը՝]] Հեռացրեց $counter ինտերվիքիի հղումները, հիմա հղումները հանդես կգան [[Wikipedia:Wikidata|Վիքիդատայում]]՝ [[d:$id|$ համարի]] տակ";
			break;
		case "ia":
			$summary = "[[Usator:Addbot|Robot:]] Migration de $counter ligamines interwiki, fornite ora per [[Wikipedia:Wikidata|Wikidatos]] in [[d:$id]]";
			break;
		case "id":
			$summary = "[[Pengguna:Addbot|Bot:]] Migrasi $counter pranala interwiki, karena telah disediakan oleh [[Wikipedia:Wikidata|Wikidata]] pada item [[d:$id]]";
			break;
		case "ie":
			$summary = "";
			break;
		case "ig":
			$summary = "";
			break;
		case "ik":
			$summary = "";
			break;
		case "ilo":
			$summary = "[[Agar-aramat:Addbot|Bot:]] Agiyal-alis kadagiti $counter nga interwiki, a nait-iteden idiay [[Wikipedia:Wikidata|Wikidata]] iti [[d:$id]]";
			break;
		case "io":
			$summary = "";
			break;
		case "is":
			$summary = "Bot: Flyt $counter tungumálatengla, sem eru núna sóttir frá [[d:|Wikidata]] á [[d:$id]]";
			break;
		case "it":
			$summary = "migrazione automatica di $counter collegamenti interwiki a [[d:Wikidata:Pagina_principale|Wikidata]], [[d:$id]]";
			break;
		case "iu":
			$summary = "";
			break;
		case "ja":
			$summary = "[[User:Addbot|ボット]]: 言語間リンク $counter 件を[[Wikipedia:ウィキデータ|ウィキデータ]]上の [[d:$id]] に転記";
			break;
		case "jbo":
			$summary = "";
			break;
		case "jv":
			$summary = "";
			break;
		case "ka":
			$summary = "[[User:Addbot|Bot:]] $counter [[ვპ:ებ|ენათაშორისი ბმული]] გადატანილია [[Wikipedia:Wikidata|Wikidata]]-ზე, [[d:$id]]";
			break;
		case "kaa":
			$summary = "";
			break;
		case "kab":
			$summary = "";
			break;
		case "kbd":
			$summary = "";
			break;
		case "kg":
			$summary = "";
			break;
		case "ki":
			$summary = "";
			break;
		case "kk":
			$summary = "";
			break;
		case "kl":
			$summary = "";
			break;
		case "km":
			$summary = "";
			break;
		case "kn":
			$summary = "";
			break;
		case "ko":
			$summary = "[[User:Addbot|봇:]] 인터위키 링크 $counter 개가 [[백:위키데이터|위키데이터]]의 [[d:$id]] 항목으로 옮겨짐";
			break;
		case "koi":
			$summary = "";
			break;
		case "krc":
			$summary = "";
			break;
		case "ks":
			$summary = "";
			break;
		case "ksh":
			$summary = "";
			break;
		case "ku":
			$summary = "Bot: $counter girêdanên înterwîkiyê ên ku niha li ser [[:d|Wikidata]]yê ne, jê bibe";
			break;
		case "kv":
			$summary = "";
			break;
		case "kw":
			$summary = "";
			break;
		case "ky":
			$summary = "";
			break;
		case "la":
			$summary = "[[Usor:Addbot|Addbot]] $counter nexus intervici removet, quod nunc apud [[d:|Vicidata]] cum tessera [[d:$id]] sunt";
			break;
		case "lad":
			$summary = "";
			break;
		case "lb":
			$summary = "Bot: Huet $counter Interwikilinke geréckelt, déi elo op [[d:|Wikidata]] op [[d:$id]] zur Verfügung gestallt ginn";
			break;
		case "lbe":
			$summary = "";
			break;
		case "lez":
			$summary = "";
			break;
		case "lg":
			$summary = "";
			break;
		case "li":
			$summary = "[[Gebruiker:Addbot|Robot:]] Verplaatsing van $counter interwikilinks. Deze staan nu op [[d:|Wikidata]] onder [[d:$id]]";
			break;
		case "lij":
			$summary = "";
			break;
		case "lmo":
			$summary = "";
			break;
		case "ln":
			$summary = "";
			break;
		case "lo":
			$summary = "";
			break;
		case "lt":
			$summary = "Perkeliamos $counter tarpkalbinės nuorodos, dabar pasiekiamos [[d:|Wikidata]] puslapyje [[d:$id]]";
			break;
		case "ltg":
			$summary = "";
			break;
		case "lv":
			$summary = "[[User:Addbot|Bots:]] pārvieto $counter starpvikipēdiju saites, kas atrodas [[d:|Vikidatos]] [[d:$id]]";
			break;
		case "map_bms":
			$summary = "";
			break;
		case "mdf":
			$summary = "";
			break;
		case "mg":
			$summary = "Nanala rohy interwiki $counter izay efa omen'i [[:mg:w:Wikipedia:Wikidata|Wikidata]] eo amin'i [[d:$id]]";
			break;
		case "mhr":
			$summary = "";
			break;
		case "mi":
			$summary = "";
			break;
		case "min":
			$summary = "[[Pengguna:Addbot|Bot:]] Migrasi $counter pautan interwiki, dek lah disadioan jo [[Wikipedia:Wikidata|Wikidata]] pado [[d:$id]]";
			break;
		case "mk":
			$summary = "[[Корисник:Addbot|Робот:]] Селам $counter меѓујазични врски во [[d:|Википодатоци]] на [[d:$id]]";
			break;
		case "ml":
			$summary = "$counter ഇന്റര്‍വിക്കി കണ്ണികളെ [[Wikipedia:Wikidata|വിക്കിഡാറ്റയിലെ]] [[d:$id]] എന്ന താളിലേക്ക്  മാറ്റിപ്പാര്‍പ്പിച്ചിരിക്കുന്നു. ";
			break;
		case "mn":
			$summary = "";
			break;
		case "mr":
			$summary = "[[सदस्य:Addbot|सांगकाम्या:]] $counter इतर भाषातील दुव्यांचे विलिनीकरण, आता [[d:WD:I|विकिडेटा]]वर उपलब्ध [[d:$id]]";
			break;
		case "mrj":
			$summary = "";
			break;
		case "ms":
			$summary = "[[Pengguna:Addbot|Bot:]] Memindahkan $counter pautan interwiki, kini disediakan oleh [[Wikipedia:Wikidata|Wikidata]] di [[d:$id]]";
			break;
		case "mt":
			$summary = "";
			break;
		case "mwl":
			$summary = "";
			break;
		case "my":
			$summary = "";
			break;
		case "myv":
			$summary = "";
			break;
		case "mzn":
			$summary = "[[کارور:Addbot|ربوت:]] $counterتا میون‌ویکی لینک دکشی‌ین، [[d:$id]] صفحه دله [[ویکی‌پدیا:ویکی‌دیتا|ویکی‌دیتا]] درون";
			break;
		case "na":
			$summary = "";
			break;
		case "nah":
			$summary = "";
			break;
		case "nap":
			$summary = "";
			break;
		case "nds":
			$summary = "[[Bruker:Addbot|Bot:]] $counter Interwikilenken, sünd nu na [[Wikipedia:Wikidata|Wikidata]] schaven [[d:$id]]";
			break;
		case "nds_nl":
			$summary = "[[Gebruiker:Addbot|Robot:]] Verplaatsing van $counter interwikilinks. Deze staan nu op [[d:|Wikidata]] onder [[d:$id]]";
			break;
		case "ne":
			$summary = "[[M:प्रयोगकर्ता:Addbot|Bot:]]  $counter अन्तरविकी लिङ्कहरु मिलाउदै, अब [[d:|विकितथ्य]]द्वारा [[d:$id]]मा प्रदान गरिएको ";
			break;
		case "new":
			$summary = "";
			break;
		case "nl":
			$summary = "[[Gebruiker:Addbot|Robot:]] Verplaatsing van $counter interwikilinks. Deze staan nu op [[d:|Wikidata]] onder [[d:$id]]";
			break;
		case "nn":
			$summary = "[[Brukar:Addbot|robot:]] fjerna $counter interwikilenkjer som er flytte til [[d:$id]] på [[Wikipedia:Wikidata|Wikidata]]";
			break;
		case "no":
			$summary = "bot: Fjerner $counter interwikilenker som nå hentes fra [[d:$id]] på [[d:|Wikidata]]";
			break;
		case "nov":
			$summary = "";
			break;
		case "nrm":
			$summary = "";
			break;
		case "nso":
			$summary = "";
			break;
		case "nv":
			$summary = "wikidata bitsʼą́ą́dę́ę́ʼígíí chodaoʼį́ kʼad ([[d:$id]]; $counter wikidata bitsʼą́ą́dę́ę́ʼ)";
			break;
		case "ny":
			$summary = "";
			break;
		case "oc":
			$summary = "";
			break;
		case "om":
			$summary = "";
			break;
		case "or":
			$summary = "";
			break;
		case "os":
			$summary = "Бот схафта $counter æвзагы æрвитæны, кæцытæ [[Википеди:Викирардтæ|Викирардты]] нырид сты ацы фарсы: [[d:$id]]";
			break;
		case "pa":
			$summary = "";
			break;
		case "pag":
			$summary = "";
			break;
		case "pam":
			$summary = "";
			break;
		case "pap":
			$summary = "";
			break;
		case "pcd":
			$summary = "";
			break;
		case "pdc":
			$summary = "";
			break;
		case "pfl":
			$summary = "[[User:Addbot|Bot:]] $counter Interwikilinks geleschd. Die braach ma nimmi, die Infos wärre nu uff [[Wikipedia:Wikidata|Wikidata]] bereitgestellt: [[d:$id]]";
			break;
		case "pi":
			$summary = "";
			break;
		case "pih":
			$summary = "";
			break;
		case "pl":
			$summary = "[[User:Addbot|Bot:]] Przenoszę linki interwiki ($counter) do [[d:|Wikidata]], są teraz dostępne do edycji na [[d:$id]]";
			break;
		case "pms":
			$summary = "";
			break;
		case "pnb":
			$summary = "";
			break;
		case "pnt":
			$summary = "";
			break;
		case "ps":
			$summary = "";
			break;
		case "pt":
			$summary = "A migrar $counter interwikis, agora providenciados por [[Wikipedia:Wikidata|Wikidata]] em [[d:$id]]";
			break;
		case "qu":
			$summary = "";
			break;
		case "rm":
			$summary = "";
			break;
		case "rmy":
			$summary = "";
			break;
		case "rn":
			$summary = "";
			break;
		case "ro":
			$summary = "Migrare a $counter legături interwiki, furnizate acum de [[Wikipedia:Wikidata|Wikidata]] la [[d:$id]]";
			break;
		case "roa_rup":
			$summary = "";
			break;
		case "roa_tara":
			$summary = "";
			break;
		case "ru":
			$summary = "Перемещение $counter интервики на [[Wikipedia:Wikidata|Викиданные]], [[d:$id]]";
			break;
		case "rue":
			$summary = "";
			break;
		case "rw":
			$summary = "";
			break;
		case "sa":
			$summary = "";
			break;
		case "sah":
			$summary = "";
			break;
		case "sc":
			$summary = "";
			break;
		case "scn":
			$summary = "";
			break;
		case "sco":
			$summary = "";
			break;
		case "sd":
			$summary = "";
			break;
		case "se":
			$summary = "";
			break;
		case "sg":
			$summary = "";
			break;
		case "sh":
			$summary = "[[Korisnik:Addbot|Bot:]] migracija $counter međuwiki veza sada dostupnih na stranici [[d:$id]] na [[d:|Wikidati]]";
			break;
		case "si":
			$summary = "";
			break;
		case "simple":
			$summary = "Bot: $counter interwiki links moved, now provided by [[d:|Wikidata]] on [[d:$id]]";
			break;
		case "sk":
			$summary = "[[Redaktor:Addbot|Bot:]] Odstránenie $counter odkazov interwiki, ktoré sú teraz dostupné na [[d:|Wikiúdajoch]] ([[d:$id]])";
			break;
		case "sl":
			$summary = "Bot: Migracija $counter interwikija/-ev, od zdaj gostuje(-jo) na [[d:|Wikipodatkih]], na [[d:$id]]";
			break;
		case "szl":
			$summary = "[[Używacz:Addbot|Addbot]] przećepoł $counter linkůw interwiki, terozki bydům ůune na [[d:|Wikidata]]";
			break;
		case "sm":
			$summary = "";
			break;
		case "sn":
			$summary = "";
			break;
		case "so":
			$summary = "";
			break;
		case "sq":
			$summary = "[[User:Addbot|Bot:]] Transferoi $counter lidhje ndërwiki, të cilat tashmë janë në dispozicion tek [[Wikipedia:Wikidata|Wikidata]] në artikullin [[d:$id]]";
			break;
		case "sr":
			$summary = "[[User:Addbot|Бот:]] Селим $counter међујезичких веза, које су сад на [[Википедија:Википодаци|Википодацима]] на [[d:$id]]";
			break;
		case "srn":
			$summary = "";
			break;
		case "ss":
			$summary = "";
			break;
		case "st":
			$summary = "";
			break;
		case "stq":
			$summary = "";
			break;
		case "su":
			$summary = "";
			break;
		case "sv":
			$summary = "Bot överför $counter interwikilänk(ar), som nu återfinns på sidan [[d:$id]] på [[d:|Wikidata]]";
			break;
		case "sw":
			$summary = "";
			break;
		case "szl":
			$summary = "";
			break;
		case "ta":
			$summary = "[[User:Addbot|தானியங்கி:]] $counter விக்கியிடை இணைப்புகள் நகர்த்தப்படுகின்றன, தற்போது [[Wikipedia:Wikidata|விக்கிதரவில்]] இங்கு [[d:$id]]";
			break;
		case "te":
			$summary = "";
			break;
		case "tet":
			$summary = "Bot: Hasai $counter ligasaun interwiki, ne'ebé agora mai husi [[d:$id]] iha [[d:|Wikidata]] ";
			break;
		case "tg":
			$summary = "";
			break;
		case "th":
			$summary = "";
			break;
		case "ti":
			$summary = "";
			break;
		case "tk":
			$summary = "";
			break;
		case "tl":
			$summary = "";
			break;
		case "tn":
			$summary = "";
			break;
		case "to":
			$summary = "";
			break;
		case "tpi":
			$summary = "";
			break;
		case "tr":
			$summary = "[[Kullanıcı:Addbot|Bot:]] Artık [[d:Wikidata:Ana_Sayfa|Vikiveri]] tarafından [[d:$id]] sayfası üzerinden sağlanan $counter vikilerarası bağlantı taşınıyor";
			break;
		case "ts":
			$summary = "";
			break;
		case "tt":
			$summary = "[[User:Addbot|Бот:]] бу мәкаләнең [[Википедия:Интервики|интервики]] сылтамалары ($counter) хәзер [[d:$id|Wikidata-да]]";
			break;
		case "tum":
			$summary = "";
			break;
		case "tw":
			$summary = "";
			break;
		case "ty":
			$summary = "";
			break;
		case "udm":
			$summary = "";
			break;
		case "ug":
			$summary = "";
			break;
		case "uk":
			$summary = "Вилучення $counter інтервікі, відтепер доступних на [[Вікіпедія:Вікідані|Вікіданих]]: [[d:$id]]";
			break;
		case "ur":
			$summary = "[[صارف:Addbot|روبالہ:]] منتقلی $counter بین الویکی روابط، اب [[d:|ویکی ڈیٹا]] میں [[d:$id]] پر موجود ہیں";
			break;
		case "uz":
			$summary = "[[Foydalanuvchi:Addbot|Bot:]] endilikda [[d:Wikidata:Ana_Sayfa|Wikidata]] [[d:$id]] sahifasida saqlanadigan $counter intervikini koʻchirdi";
			break;
		case "ve":
			$summary = "";
			break;
		case "vec":
			$summary = "[[Utente:Addbot|Bot]]: Migrasion de $counter interwiki links so [[d:Wikidata:Pagina_principale|Wikidata]] - [[d:$id]]";
			break;
		case "vep":
			$summary = "";
			break;
		case "vi":
			$summary = "Bot: Di chuyển $counter liên kết ngôn ngữ đến [[d:$id]] tại [[d:|Wikidata]] ([[m:User:Addbot/WDS|Addbot]])";
			break;
		case "vls":
			$summary = "";
			break;
		case "vo":
			$summary = "";
			break;
		case "wa":
			$summary = "";
			break;
		case "war":
			$summary = "";
			break;
		case "wo":
			$summary = "";
			break;
		case "wuu":
			$summary = "";
			break;
		case "xal":
			$summary = "";
			break;
		case "xh":
			$summary = "";
			break;
		case "xmf":
			$summary = "";
			break;
		case "yi":
			$summary = "";
			break;
		case "yo":
			$summary = "";
			break;
		case "za":
			$summary = "";
			break;
		case "zea":
			$summary = "[[Gebruiker:Addbot|Robot:]] Verplaatsing van $counter interwikilinks. Deze staan nu op [[d:|Wikidata]] onder [[d:$id]]";
			break;
		case "zh":
			$summary = "机器人：移除" . $counter . "个跨语言链接，现在由[[d:|维基数据]]的[[d:" . $id . "]]提供。";
			break;
		case "zh_classical":
			$summary = "";
			break;
		case "zh_min_nan":
			$summary = "";
			break;
		case "zh_yue":
			$summary = "";
			break;
		case "zu":
			$summary = "";
			break;
		default:
			$summary = "[[M:User:Addbot|Bot:]] Migrating $counter interwiki links, now provided by [[d:|Wikidata]] on [[d:$id]] [[M:User:Addbot/WDS|(translate me)]]";
	} //close case select
	if($summary == "")
	{
		$summary = "[[M:User:Addbot|Bot:]] Migrating $counter interwiki links, now provided by [[d:|Wikidata]] on [[d:$id]] [[M:User:Addbot/WDS|(translate me)]]";
	}
	return $summary;
} //close function
function getmyrem($lang)
{
	$remove = "";
	//Set language specific options
	switch ($lang) {
		case "en":
			$remove = "<!-- ?interwikis?( links?)? ?-->";
			break;
		case "fr":
			$remove = "<!-- ?((liens? )?inter(wiki|langue)s?|other languages) ?-->";
			break;
		case "bn":
			$remove = "<!-- ?আন্তঃউইকিসমূহ?( সংযোগ?)? ?-->";
			break;
		case "hr":
			$remove = "<!-- ?(interwiki|internacionalni linkovi|međuwiki poveznice) ?-->";
			break;
		case "nn":
			$remove = "<!--interwiki \( ?no ?(\/ ?nb)?, ?sv ?, ?da first; then other languages alphabetically by name\)-->";
			break;
		case "simple":
			$remove = "<!-- ?interwikis?( links?)? ?-->";
			break;
		case "ta":
			$remove = "<!-- ?விக்கியிடைகள்?( இணைப்புகள்?)? ?-->";
			break;
		case "tr":
			$remove = "<!-- ?((links? )?inter(wiki|viki)s?|diğer diller) ?-->";
			break;
	} //close case select
	return $remove;
} //close function
?>
