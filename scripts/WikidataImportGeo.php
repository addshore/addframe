<?php
namespace Addwiki;

use Addframe\Entity;
use Addframe\Family;
use Addframe\Globals;
use Addframe\Mysql;
use Addframe\Page;
use Addframe\UserLogin;
use Addframe\Stathat;
use Addframe\Site;

require_once( dirname( __FILE__ ) . '/../init.php' );

//php script.php --site='enwiki'
//$options['site']
$options = getopt("", Array(
	"site::"
));

$stathat = new Stathat( Globals::$config['stathat']['key'] );

$wm = new Family( new UserLogin( Globals::$config['wikiuser']['username'], Globals::$config['wikiuser']['password'] ), Globals::$config['wikiuser']['home'] );
$wiki = $wm->getSiteFromSiteid( $options['site'] );
if( !$wiki instanceof Site ){
	die("No such wiki");
}

$db = new Mysql(
	$options['site'].'.labsdb', '3306',
	Globals::$config['mysql']['user'],
	Globals::$config['mysql']['password'],
	$options['site'].'_p' );

$offset = 0;

$sources = array( 'tr' => "58255", 'lg' => "8566347", 'cy' => "848525", 'sm' => "8571427", 'pnb' => "3696028", 'st' => "8572199", 'cr' => "8561582", 'ro' => "199864", 'vep' => "4107346", 'fiu-vro' => "1585232", 'ha' => "8563393", 'scn' => "1058430", 'roa-rup' => "2073394", 'az' => "58251", 'wa' => "1132977", 'xal' => "4210231", 'rmy' => "8571143", 'ln' => "8566298", 'kn' => "3181422", 'mhr' => "824297", 'nl' => "10000", 'be' => "877583", 'ta' => "844491", 'ang' => "8558960", 'cdo' => "846630", 'br' => "846871", 'pdc' => "3025736", 'dz' => "8561662", 'fo' => "8042979", 'lad' => "3756562", 'ne' => "8560590", 'ti' => "8575467", 'zh-min-nan' => "3239456", 'vls' => "3568038", 'fur' => "3568039", 'os' => "226150", 'lez' => "45041", 'ky' => "60799", 'ks' => "8565447", 'lbe' => "6587084", 'ak' => "8558731", 'kbd' => "13231253", 'vi' => "200180", 'simple' => "200183", 'zh-yue' => "1190962", 'koi' => "1116066", 'fi' => "175482", 'got' => "8563136", 'wo' => "8582589", 'mn' => "2998037", 'vo' => "714826", 'jv' => "3477935", 'fa' => "48952", 'ab' => "3568035", 'uz' => "2081526", 'di' => "38288", 'ug' => "60856", 'yo' => "1148240", 'te' => "848046", 'pcd' => "3568053", 'tl' => "877685", 'bat-smg' => "3568069", 'pi' => "8570791", 'an' => "1147071", 'sd' => "8571840", 'sw' => "722243", 'frr' => "8669146", 'ltg' => "2913253", 'ff' => "8562927", 'mi' => "2732019", 'glk' => "3944107", 'pag' => "12265494", 'ceb' => "837615", 'pfl' => "13358221", 'bo' => "2091593", 'ss' => "3432470", 'pms' => "3046353", 'gv' => "8566503", 'nds-nl' => "1574617", 'tet' => "8575385", 'ext' => "3181928", 'bcl' => "8561870", 'om' => "8570425", 'tn' => "3568063", 'hr' => "203488", 'roa-tara' => "3568062", 'u' => "1377618", 'el' => "11918", 'frp' => "8562529", 'mk' => "842341", 'ckb' => "4115463", 'bg' => "11913", 'oc' => "595628", 'ig' => "8563635", 'av' => "5652665", 'tw' => "8575885", 'sv' => "169514", 'su' => "966609", 'myv' => "856881", 'tpi' => "571001", 'en' => "328", 'ht' => "1066461", 'bpy' => "1287192", 'als' => "1211233", 'sl' => "14380", 'mt' => "3180091", 'ia' => "3757068", 'arz' => "2374285", 'csb' => "3756269", 'ie' => "6167360", 'crh' => "60786", 'ba' => "58209", 'it' => "11920", 'pt' => "11921", 'sa' => "2587255", 'jbo' => "8566311", 'rm' => "3026819", 'ru' => "206855", 'mr' => "3486726", 'ja' => "177837", 'no' => "191769", 'af' => "766705", 'mg' => "3123304", 'hsb' => "2402143", 'is' => "718394", 'gn' => "3807895", 'nah' => "2744155", 'bi' => "8561332", 'de' => "48183", 'cs' => "191168", 'sk' => "192582", 'kw' => "8565801", 'tum' => "8575782", 'ar' => "199700", 'ts' => "8575674", 'kk' => "58172", 'eo' => "190551", 'to' => "3112631", 'dv' => "928808", 'ce' => "4783991", 'gd' => "8562272", 'ki' => "8565476", 'hif' => "8562481", 'ast' => "1071918", 'id' => "155214", 'cv' => "58215", 'my' => "4614845", 'co' => "3111179", 'zea' => "2111591", 'fj' => "8562502", 'new' => "1291627", 'ch' => "8576190", 've' => "8577029", 'tk' => "511754", 'ml' => "874555", 'mwl' => "8568791", 'nov' => "8570353", 'or' => "7102897", 'nv' => "8569757", 's' => "208533", 'lv' => "728945", 'lb' => "950058", 'sh' => "58679", 'war' => "1648786", 'iu' => "3913095", 'so' => "8572132", 'mdf' => "1178461", 'bm' => "8559737", 'kg' => "8565463", 'tt' => "60819", 'ace' => "3957795", 'pih' => "8570048", 'hy' => "1975217", 'sn' => "8571809", 'na' => "3753095", 'kab' => "8564352", 'lt' => "202472", 'zu' => "8075204", 'zh-classical' => "1378484", 'sr' => "200386", 'hi' => "722040", 'mzn' => "3568048", 'sco' => "1444686", 'nap' => "1047851", 'bs' => "1047829", 'la' => "12237", 'szl' => "940309", 'za' => "3311132", 'nn' => "2349453", 'ms' => "845993", 'da' => "181163", 'min' => "4296423", 'wuu' => "1110233", 'eml' => "3568066", 'xh' => "3568065", 'hak' => "6112922", 'pam' => "588620", 'ty' => "3568061", 'srn' => "3568060", 'et' => "200060", 'nds' => "4925786", 'he' => "199913", 'ku' => "1154741", 'rn' => "8565742", 'nso' => "13230970", 'dsb' => "8561147", 'kv' => "925661", 'se' => "4115441", 'zh' => "30239", 'gan' => "6125437", 'io' => "1154766", 'fy' => "2602203", 'arc' => "8569951", 'pa' => "1754193", 'gu' => "3180306", 'gag' => "79633", 'pnt' => "4372058", 'pl' => "1551807", 'vec' => "1055841", 'ik' => "8563863", 'as' => "8559119", 'ay' => "3826575", 'lmo' => "3913160", 'ko' => "17985", 'sg' => "8571487", 'pap' => "3568056", 'ps' => "3568054", 'ga' => "875631", 'sc' => "3568059", 'yi' => "1968379", 'ny' => "8561552", 'bxr' => "8561415", 'rue' => "58781", 'sah' => "225594", 'mrj' => "1034940", 'ilo' => "8563685", 'chr' => "8576237", 'krc' => "1249553", 'map-bms' => "4077512", 'ur' => "1067878", 'si' => "8571954", '' => "2029239", 'th' => "565074", 'tg' => "2742472", 'cbk-zam' => "8575930", 'nrm' => "3568051", 'ee' => "8562097", 'fr' => "8447", 'bn' => "427715", 'bug' => "4097773", 'gl' => "841208", 'be-x-old' => "8937989", 'chy' => "8561491", 'es' => "8449", 'bar' => "1961887", 'lo' => "3568045", 'bh' => "8561277", 'lij' => "3568046", 'ksh' => "3568041", 'st' => "3568040", 'haw' => "3568043", 'kl' => "3568042", 'kaa' => "79636", 'bjn' => "2983979", 'hu' => "53464", 'mo' => "3568049", 'eu' => "207260", 'am' => "3025527", 'li' => "2328409", 'ca' => "199693", 'km' => "3568044", 'udm' => "221444", 'cu' => "547271", 'rw' => "8565518", 'uk' => "199698", 'ka' => "848974" );

while (true){

	echo "#";
	$list = $db->mysql2array( $db->doQuery("select page_title as title, page_namespace as namespace from geo_tags,page where gt_page_id = page_id limit 100 offset ".$offset) );
		$offset = $offset + 100;
	if( !count( $list ) > 0 ){
		die();
	}

	foreach($list as $page ){
		echo ".";
		if($page['namespace'] != '0'){
			continue;
		}
		$page = $wiki->newPageFromTitle( $page['title'] );
		if( !$page instanceof Page){
			continue;
		}

		$coordArray = $page->getCoordinates();
		if( is_array( $coordArray ) ){
			$entity = $page->getEntity();
			//if it has an entity
			if ( $entity instanceof Entity ) {

				//skip if not a place gnd
				$gndClaims = $entity->getClaims( 'p107' );
				if( array_key_exists( 'p107', $gndClaims ) ){
					$gnd = $gndClaims['p107']['0']['mainsnak']['datavalue']['value']['numeric-id'];
				} else {
					$gnd = '';
				}
				if( $gnd != '618123'){
					continue;
				}

				$startClaims = $entity->getClaims( 'p625' );
				//if there are no coords already
				if( count($startClaims) == 0 ){
					$ourCoord = getWdCoordFromWiki( $coordArray );
					//if we have a coors
					if ( is_array( $ourCoord ) ) {
						//add the claim
						$result = $entity->createClaim( 'value', 'p625', json_encode( $ourCoord ) );
						echo $entity->id;
						$stathat->stathat_ez_count( "Addbot - AddGeo", 1 );
						//if we can find a id for the ref
						if( array_key_exists( $page->site->getLanguage(), $sources ) ){
							if ( isset( $result['claim']['id'] ) ) {
								$ref['snaktype'] = 'value';
								$ref['property'] = 'p143';
								$ref['datavalue'] = array( 'type' => 'wikibase-entityid', 'value' => array( 'entity-type' => 'item', 'numeric-id' => intval( $sources[$page->site->getLanguage()] ) ) );
								$refJson = '{"' . $ref['property'] . '":[' . json_encode( $ref ) . ']}';
								//add it
								$result = $entity->site->requestWbSetReference( array( 'statement' => $result['claim']['id'], 'snaks' => $refJson ) );
							}
						}
						
					} 
				}

			}
		}
	}
}

//Function to convert an array of coords from wiki to a single coord for wikidata
function getWdCoordFromWiki( $array ) {
	$newArray = array();

	foreach ( $array as $key => $coord ) {
		$newArray[$key]['latitude'] = $coord['lat'];
		$newArray[$key]['longitude'] = $coord['lon'];
		if( array_key_exists( 'dim', $coord) ){
			$newArray[$key]['dimension'] = $coord['dim'];
		}
		if( array_key_exists( 'globe', $coord) ){
			$newArray[$key]['globe'] = $coord['globe'];
		}
		$p = max( strlen(substr(strrchr($coord['lat'], "."), 1)), strlen(substr(strrchr($coord['lon'], "."), 1)));
		if($p > 15 || $p < 1){ return null; }
		$calc = str_repeat('0',$p);
		$calc = $calc.str_repeat('9', 15-strlen($calc) );
		$calc = '0.'.$calc;
		if( strlen($calc) != 17){ return null; }
		$newArray[$key]['precision'] = $calc;

		if ( array_key_exists( 'primary', $coord ) ) {
			return $newArray[$key];
		}
	}

	foreach ( $newArray as $toReturn ) {
		return $toReturn;
	}
	return null;
}