<?php

/*
This file create an array for all namespaces for all language wikipedias listed below.
This file also gets all aliases for said namespaces
The file currently outputs in a rather large text version of an array which can easily be imported into scripts
*/

//requires
require __DIR__.'/../classes/botclasses.php';
require __DIR__.'/../config/wiki.php';

//list of wikipedia languages split with spaces
$langs = "nostalgia|ten|aa|ab|ace|af|ak|als|am|an|ang|ar|arc|arz|as|ast|av|ay|az|ba|bar|bat-smg|bcl|be|be-x-old|bg|bh|bi|bjn|bm|bn|bo|bpy|br|bs|bug|bxr|ca|cbk-zam|cdo|ce|ceb|ch|cho|chr|chy|ckb|co|cr|crh|cs|csb|cu|cv|cy|da|de|diq|dsb|dv|dz|ee|el|eml|en|eo|es|et|eu|ext|fa|ff|fi|fiu-vro|fj|fo|fr|frp|frr|fur|fy|ga|gag|gan|gd|gl|glk|gn|got|gu|gv|ha|hak|haw|he|hi|hif|ho|hr|hsb|ht|hu|hy|hz|ia|id|ie|ig|ii|ik|ilo|io|is|it|iu|ja|jbo|jv|ka|kaa|kab|kbd|kg|ki|kj|kk|kl|km|kn|ko|koi|kr|krc|ks|ksh|ku|kv|kw|ky|la|lad|lb|lbe|lez|lg|li|lij|lmo|ln|lo|lt|ltg|lv|map-bms|mdf|mg|mh|mhr|mi|min|mk|ml|mn|mo|mr|mrj|ms|mt|mus|mwl|my|myv|mzn|na|nah|nap|nds|nds-nl|ne|new|ng|nl|nn|no|nov|nrm|nso|nv|ny|oc|om|or|os|pa|pag|pam|pap|pcd|pdc|pfl|pi|pih|pl|pms|pnb|pnt|ps|pt|qu|rm|rmy|rn|ro|roa-rup|roa-tara|ru|rue|rw|sa|sah|sc|scn|sco|sd|se|sg|sh|si|simple|sk|sl|sm|sn|so|sq|sr|srn|ss|st|stq|su|sv|sw|szl|ta|te|tet|tg|th|ti|tk|tl|tn|to|tpi|tr|ts|tt|tum|tw|ty|udm|ug|uk|ur|ve|vec|vep|vi|vls|vo|wa|war|wo|wuu|xal|xh|xmf|yi|yo|za|zea|zh|zh-classical|zh-min-nan|zh-yue|zu";
$langs = explode('|',$langs);

$namespaces = Array();

foreach($langs as $lang)
{
	$namespaces[$lang] = Array();
	$wiki = new wikipedia;
	$wiki->url = "http://$lang.wikipedia.org/w/api.php";
	global $wiki;
	echo "\nGetting from $lang.wikipedia.org";
	//echo "\nLogging in to $lang.wikipedia.org...";
	//$wiki->login($config['user'],$config['password']);
	
	$x = $wiki->query('?action=query&meta=siteinfo&siprop=namespaces|namespacealiases&format=php');
	foreach ($x['query']['namespaces'] as $ns)
	{
		$namespaces[$lang][$ns['id']] = Array();
		if($ns['id'] != 0){array_push($namespaces[$lang][$ns['id']],$ns['canonical']);}
		array_push($namespaces[$lang][$ns['id']],$ns['*']);

	}
	foreach ($x['query']['namespacealiases'] as $ns)
	{
		array_push($namespaces[$lang][$ns['id']],$ns['*']);
	}
}

//output

$wiki = new wikipedia;
$wiki->url = "http://en.wikipedia.org/w/api.php";
global $wiki;
echo "\nLogging in to en.wikipedia.org...";
$wiki->login($config['user'],$config['password']);

$out = "<?\n\n";

foreach($namespaces as $key1 => $lang)
{
	foreach($lang as $key2 => $ns)
	{
		foreach($ns as $key3 => $n)
		{
			$out .= '$ns'."['$key1']['$key2']['$key3'] = ".'"'."$n".'"'.";\n";
		}
	}
}

$out .= "\n?>";
$wiki->edit("User:".$config['user']."/Sandbox",$out,"Posting dump of all namespaces",true,true,null,true,"0");

?>
