<?
//Get our settings
$options = getopt("",Array("file::"));
require '/data/project/addbot/enwiki/config.php';
require '/data/project/addbot/classes/database.php';
$file = $options['file']; //e.g. itwiki-20130131-pages-articles.xml.bz2
$fsplit = explode('-',$file);
$file = "/public/datasets/public/".$fsplit[0]."/".$fsplit[1]."/$file";
$lang = str_replace("wiki","",$fsplit[0]);
echo $file."\n";
unset($options,$fsplit);

//Parse the dump
$bz = bzopen($file, "r") or die("Couldn't open $file");
$temp = tempnam(sys_get_temp_dir(), "glist");
$parts = Array();
while(!feof($bz)) {
  $buffer = bzread($bz, 4096);
  if($buffer === FALSE) die('Read problem');
  //file_put_contents($temp,$buffer,FILE_APPEND)
  array_push($parts,$buffer);
  echo ".";
}
bzclose($bz);
$p = xml_parser_create();
xml_parse_into_struct($p, implode($parts), $vals, $index);
unset($p);
unset($parts);

//Find the article we want
$list = Array();
foreach ($index['TEXT'] as $key => $item)
{
	if(preg_match('/\[\[(nostalgia|ten|test|aa|ab|ace|af|ak|als|am|an|ang|ar|arc|arz|as|ast|av|ay|az|ba|bar|bat-smg|bcl|be|be-x-old|bg|bh|bi|bjn|bm|bn|bo|bpy|br|bs|bug|bxr|ca|cbk-zam|cdo|ce|ceb|ch|cho|chr|chy|ckb|co|cr|crh|cs|csb|cu|cv|cy|da|de|diq|dsb|dv|dz|ee|el|eml|eo|es|et|eu|ext|fa|ff|fi|fiu-vro|fj|fo|fr|frp|frr|fur|fy|ga|gag|gan|gd|gl|glk|gn|got|gu|gv|ha|hak|haw|he|hi|hif|ho|hr|hsb|ht|hu|hy|hz|ia|id|ie|ig|ii|ik|ilo|io|is|it|iu|ja|jbo|jv|ka|kaa|kab|kbd|kg|ki|kj|kk|kl|km|kn|ko|koi|kr|krc|ks|ksh|ku|kv|kw|ky|la|lad|lb|lbe|lez|lg|li|lij|lmo|ln|lo|lt|ltg|lv|map-bms|mdf|mg|mh|mhr|mi|min|mk|ml|mn|mo|mr|mrj|ms|mt|mus|mwl|my|myv|mzn|na|nah|nap|nds|nds-nl|ne|new|ng|nl|nn|no|nov|nrm|nso|nv|ny|oc|om|or|os|pa|pag|pam|pap|pcd|pdc|pfl|pi|pih|pl|pms|pnb|pnt|ps|pt|qu|rm|rmy|rn|ro|roa-rup|roa-tara|ru|rue|rw|sa|sah|sc|scn|sco|sd|se|sg|sh|si|simple|sk|sl|sm|sn|so|sq|sr|srn|ss|st|stq|su|sv|sw|szl|ta|te|tet|tg|th|ti|tk|tl|tn|to|tpi|tr|ts|tt|tum|tw|ty|udm|ug|uk|ur|ve|vec|vep|vi|vls|vo|wa|war|wo|wuu|xal|xh|xmf|yi|yo|za|zea|zh|zh-classical|zh-min-nan|zh-yue|zu):[^\]]+\]\]/i',$vals[$item]['value']))
	{
		$title = $vals[$index['TITLE'][$key]]['value'];
		array_push($list,$vals[$index['TITLE'][$key]]['value']);
		echo $title."\n";
	}
}
unset($index,$vals);

//Save the list to DB
$db = new Database( $config['dbhost'], $config['dbport'], $config['dbuser'], $config['dbpass'], $config['dbname'], false);
foreach($list as $item) // for each item
{
	if( $item != "")
	{
		usleep(100);
		$res = $db->insert('iwlinked',array('lang' => $lang,'article' => $item,) ); // inset to database table
		if( !$res  ){echo $db->errorStr()."\n";} // if no result then break as we have an error ($db->errorStr())
		else{echo "Added ".$item." to database\n";}
	}
}
?>

