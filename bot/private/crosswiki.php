<?php
$edit = Array();
//array_push($edit,Array("name"=>"User:Addbot","text"=>"{{bot|Addshore}}","summ"=>"Adding {{bot}} template"));
//array_push($edit,Array("name"=>"User:Legobot","text"=>"{{bot|Legoktm}}\n[[File:Redirectltr.png|#REDIRECT|link=]][[:meta:User:Legobot]]","summ"=>"Adding {{bot}} template"));
//array_push($edit,Array("name"=>"User talk:Legobot","text"=>"#REDIRECT[[Meta:User talk:Legoktm]]","summ"=>"Redirecting to Meta talk page"));
//array_push($edit,Array("name"=>"User talk:Addbot","text"=>"#REDIRECT[[User talk:Addshore]]","summ"=>"Redirecting to operator page"));
//array_push($edit,Array("name"=>"User:Addshore","text"=>softredirect("meta:User:Addshore"),"summ"=>"Redirecting to my meta page"));
//array_push($edit,Array("name"=>"User talk:Addshore","text"=>softredirect("meta:User talk:Addshore"),"summ"=>"Redirecting to my meta page"));
//array_push($edit,Array("name"=>"User:Addshore/common.js","text"=>"mw.loader.load('//meta.wikimedia.org/w/index.php?title=User:Addshore/global.js&action=raw&ctype=text/javascript');","summ"=>"Linking to my global js file"));
array_push($edit,Array("name"=>"User:Addbot","text"=>softredirect("meta:User:Addbot"),"summ"=>"Adding soft redirect to meta page"));

error_reporting(E_ALL ^ E_NOTICE);
require __DIR__.'/../../classes/botclasses.php';
$langs = Array('ab','ace','af','ak','als','am','an','ang','ar','arc','arz','as','ast','av','ay','az','ba','bar','bat-smg','bcl','be','be-x-old','bg','bh','bi','bjn','bm','bn','bo','bpy','br','bs','bug','bxr','ca','cbk-zam','cdo','ce','ceb','ch','chr','chy','ckb','co','cr','crh','cs','csb','cu','cv','cy','da','de','diq','dsb','dv','dz','ee','el','eml','en','eo','es','et','eu','ext','fa','ff','fi','fiu-vro','fj','fo','fr','frp','frr','fur','fy','ga','gag','gan','gd','gl','glk','gn','got','gu','gv','ha','hak','haw','he','hi','hif','hr','hsb','ht','hu','hy','ia','id','ie','ig','ik','ilo','io','is','it','iu','ja','jbo','jv','ka','kaa','kab','kbd','kg','ki','kk','kl','km','kn','ko','koi','krc','ks','ksh','ku','kv','kw','ky','la','lad','lb','lbe','lez','lg','li','lij','lmo','ln','lo','lt','ltg','lv','map-bms','mdf','mg','mhr','mi','min','mk','ml','mn','mr','mrj','ms','mt','mwl','my','myv','mzn','na','nah','nap','nds','nds-nl','ne','new','nl','nn','no','nov','nrm','nso','nv','ny','oc','om','or','os','pa','pag','pam','pap','pcd','pdc','pfl','pi','pih','pl','pms','pnb','pnt','ps','pt','qu','rm','rmy','rn','ro','roa-rup','roa-tara','ru','rue','rw','sa','sah','sc','scn','sco','sd','se','sg','sh','si','simple','sk','sl','sm','sn','so','sq','sr','srn','ss','st','stq','su','sv','sw','szl','ta','te','tet','tg','th','ti','tk','tl','tn','to','tpi','tr','ts','tt','tum','tw','ty','udm','ug','uk','ur','uz','ve','vec','vep','vi','vls','vo','wa','war','wo','wuu','xal','xh','xmf','yi','yo','za','zea','zh','zh-classical','zh-min-nan','zh-yue','zu');
foreach ($langs as $lang)
{
	echo $lang;
	if(preg_match('/^(en|he)$/',$lang)){continue;echo " > Skiped\n";}
	$wiki = new wikipedia;
	$wiki->url = "http://$lang.wikipedia.org/w/api.php";
	$wiki->login("user","pass");
	echo " > Logged in";

	foreach ($edit as $e)
	{
		sleep(1);
		$t = $wiki->getpage($e['name']);
		if(strlen($t) > 1)
		{
			$res = $wiki->edit($e['name'],$t."\n".$e['text'],$e['summ'],true,false);
		}
		echo " > ".$e['name']." ".$res['edit']['result'];
	}

	echo " > Done\n"; 
	unset($wiki);
	sleep(2);
}

//Functions below

function softredirect($target)
{
	return "<div style='min-height: 57px; border: 1px solid #aaaaaa; background-color: #f9f9f9; width: 50%; margin: 0 auto 1em auto; padding: .2em; text-align: justify;'>
<div style='float: left'>[[File:Wiki.png|50px|link=]]</div>
<div style='margin-left: 60px'>This page can be found at '''[[:$target|$target]]'''.<br />''This is an [[meta:Soft redirect|interwiki redirect]]''.</div>
</div>";
}

?>
