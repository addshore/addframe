<?
//requires
require '/data/project/addbot/classes/botclasses.php';
require '/data/project/addbot/config/wiki.php';

//list of wikipedia languages split with spaces
$langs = "ab ace af als am an ang ar arc arz as ast av ay az ba bar bcl be bg bh bi bjn bm bn bo bpy br bs bug bxr ca cdo ce ceb chr ckb co cr crh cs csb cu cv cy da de diq dsb dv dz ee el eml en eo es et eu ext fa ff fi fj fo fr frp frr fur fy ga gag gan gd gl glk gn got gu gv ha hak haw hi hif hr hsb ht hu hy ia id ie ig ik ilo io is it iu ja jbo jv ka kaa kab kbd kg ki kk kl km kn ko koi krc ks ksh ku kv kw ky la lad lb lbe lez li lij lmo ln lo lt ltg lv mdf mg mhr mi min mk ml mn mo mr mrj ms mt mwl my mzn na nah nap nds ne new nl nn no nov nrm nso nv ny oc or os pa pam pap pcd pdc pfl pi pih pl pms pnb pnt ps pt qu rm rmy rn ro ru rue rw sa sah sc scn sco sd se sg sh si simple sk sl sm sn so sq sr srn ss stq su sv sw szl ta te tet tg th ti tk tl tn to tpi tr ts tt tum ty udm ug uk ur uz ve vec vep vi vls vo wa war wo wuu xal yi yo zea zh zu";
$langs = explode(' ',$langs);

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
			$out .= '$ns'."['$key1']['$key2']['$key3'] = $n;\n";
		}
	}
}

$out .= "\n?>";
$wiki->edit("User:Addshore/Sandbox",$out,"Posting dump of all namespaces",true,true,null,true,"0");

?>
