<?php
namespace Addwiki;

use Addframe\Entity;
use Addframe\Family;
use Addframe\Globals;
use Addframe\Mysql;
use Addframe\Page;
use Addframe\UserLogin;

require_once( dirname( __FILE__ ) . '/../init.php' );

$wm = new Family( new UserLogin( Globals::$config['wikiuser']['username'], Globals::$config['wikiuser']['password'] ), Globals::$config['wikiuser']['home'] );
$wiki = $wm->getSite( 'en.wikipedia.org' );

$db = new Mysql(
	'enwiki.labsdb', '3306',
	Globals::$config['mysql']['user'],
	Globals::$config['mysql']['password'],
	'enwiki_p' );

echo "Doing database query\n";
$list = $db->mysql2array( $db->doQuery("select page_title as title, page_namespace as namespace from geo_tags,page where gt_page_id = page_id limit 5") );


foreach($list as $page ){
	if($page['ns'] != '0'){
		continue;
	}
	$page = $wiki->newPageFromTitle( $page['title'] );
	if( !$page instanceof Page){
		continue;
	}

	echo "Loading page " . $page->title . "\n";
	$coordArray = $page->getCoordinates();
	if( is_array( $coordArray ) ){
		$entity = $page->getEntity();
		//if it has an entity
		if ( $entity instanceof Entity ) {
			$entity->id = "Q4115189";
			echo "Found Entity ".$entity->id."\n";

			//skip if not a place gnd
			$gndClaims = $entity->getClaims( 'p107' );
			if( array_key_exists( 'p107', $gndClaims ) ){
				$gnd = $gndClaims['p107']['0']['mainsnak']['datavalue']['value']['numeric-id'];
			} else {
				$gnd = '';
			}
			if( $gnd !== '618123'){
				echo "Note correct GND\n";
				continue;
			}

			$startClaims = $entity->getClaims( 'p625' );
			//if there are no coords already
			if( count($startClaims) == 0 ){
				$ourCoord = getWdCoordFromWiki( $coordArray );
				//if we have a coors
				if ( is_array( $ourCoord ) ) {
					echo "Adding coord " . json_encode( $ourCoord ) . "\n";
					//add the claim
					$result = $entity->createClaim( 'value', 'p625', json_encode( $ourCoord ) );
					if ( array_key_exists( 'id', $result['claim'] ) ) {
						//if we can find a id for the ref
						$refId = getWikiSource( $page->site->getLanguage() );
						if ( $refId !== null ) {
							$ref['snaktype'] = 'value';
							$ref['property'] = 'p143';
							$ref['datavalue'] = array( 'type' => 'wikibase-entityid', 'value' => array( 'entity-type' => 'item', 'numeric-id' => intval( trim( $refId, 'Q' ) ) ) );
							$ref = '{"' . $ref['property'] . '":[' . json_encode( $ref ) . ']}';
							//add it
							echo "Adding reference " . $ref . "\n";
							$result = $entity->site->requestWbSetReference( array( 'statement' => $result['claim']['id'], 'snaks' => $ref ) );
							print_r( $result );
						}
					}
				} else {
					echo "No coord got from the api\n";
				}
			} else {
				echo "Not adding as already contains " . count( $startClaims ) . " coords\n";
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
		$newArray[$key]['globe'] = $coord['globe'];

		if ( array_key_exists( 'primary', $coord ) ) {
			return $newArray[$key];
		}
	}

	foreach ( $newArray as $toReturn ) {
		return $toReturn;
	}
	return null;
}

function getWikiSource( $lang ) {
	$sources = array( 'tr' => "Q58255", 'lg' => "Q8566347", 'cy' => "Q848525", 'sm' => "Q8571427", 'pnb' => "Q3696028", 'st' => "Q8572199", 'cr' => "Q8561582", 'ro' => "Q199864", 'vep' => "Q4107346", 'fiu-vro' => "Q1585232", 'ha' => "Q8563393", 'scn' => "Q1058430", 'roa-rup' => "Q2073394", 'az' => "Q58251", 'wa' => "Q1132977", 'xal' => "Q4210231", 'rmy' => "Q8571143", 'ln' => "Q8566298", 'kn' => "Q3181422", 'mhr' => "Q824297", 'nl' => "Q10000", 'be' => "Q877583", 'ta' => "Q844491", 'ang' => "Q8558960", 'cdo' => "Q846630", 'br' => "Q846871", 'pdc' => "Q3025736", 'dz' => "Q8561662", 'fo' => "Q8042979", 'lad' => "Q3756562", 'ne' => "Q8560590", 'ti' => "Q8575467", 'zh-min-nan' => "Q3239456", 'vls' => "Q3568038", 'fur' => "Q3568039", 'os' => "Q226150", 'lez' => "Q45041", 'ky' => "Q60799", 'ks' => "Q8565447", 'lbe' => "Q6587084", 'ak' => "Q8558731", 'kbd' => "Q13231253", 'vi' => "Q200180", 'simple' => "Q200183", 'zh-yue' => "Q1190962", 'koi' => "Q1116066", 'fi' => "Q175482", 'got' => "Q8563136", 'wo' => "Q8582589", 'mn' => "Q2998037", 'vo' => "Q714826", 'jv' => "Q3477935", 'fa' => "Q48952", 'ab' => "Q3568035", 'uz' => "Q2081526", 'diq' => "Q38288", 'ug' => "Q60856", 'yo' => "Q1148240", 'te' => "Q848046", 'pcd' => "Q3568053", 'tl' => "Q877685", 'bat-smg' => "Q3568069", 'pi' => "Q8570791", 'an' => "Q1147071", 'sd' => "Q8571840", 'sw' => "Q722243", 'frr' => "Q8669146", 'ltg' => "Q2913253", 'ff' => "Q8562927", 'mi' => "Q2732019", 'glk' => "Q3944107", 'pag' => "Q12265494", 'ceb' => "Q837615", 'pfl' => "Q13358221", 'bo' => "Q2091593", 'ss' => "Q3432470", 'pms' => "Q3046353", 'gv' => "Q8566503", 'nds-nl' => "Q1574617", 'tet' => "Q8575385", 'ext' => "Q3181928", 'bcl' => "Q8561870", 'om' => "Q8570425", 'tn' => "Q3568063", 'hr' => "Q203488", 'roa-tara' => "Q3568062", 'qu' => "Q1377618", 'el' => "Q11918", 'frp' => "Q8562529", 'mk' => "Q842341", 'ckb' => "Q4115463", 'bg' => "Q11913", 'oc' => "Q595628", 'ig' => "Q8563635", 'av' => "Q5652665", 'tw' => "Q8575885", 'sv' => "Q169514", 'su' => "Q966609", 'myv' => "Q856881", 'tpi' => "Q571001", 'en' => "Q328", 'ht' => "Q1066461", 'bpy' => "Q1287192", 'als' => "Q1211233", 'sl' => "Q14380", 'mt' => "Q3180091", 'ia' => "Q3757068", 'arz' => "Q2374285", 'csb' => "Q3756269", 'ie' => "Q6167360", 'crh' => "Q60786", 'ba' => "Q58209", 'it' => "Q11920", 'pt' => "Q11921", 'sa' => "Q2587255", 'jbo' => "Q8566311", 'rm' => "Q3026819", 'ru' => "Q206855", 'mr' => "Q3486726", 'ja' => "Q177837", 'no' => "Q191769", 'af' => "Q766705", 'mg' => "Q3123304", 'hsb' => "Q2402143", 'is' => "Q718394", 'gn' => "Q3807895", 'nah' => "Q2744155", 'bi' => "Q8561332", 'de' => "Q48183", 'cs' => "Q191168", 'sk' => "Q192582", 'kw' => "Q8565801", 'tum' => "Q8575782", 'ar' => "Q199700", 'ts' => "Q8575674", 'kk' => "Q58172", 'eo' => "Q190551", 'to' => "Q3112631", 'dv' => "Q928808", 'ce' => "Q4783991", 'gd' => "Q8562272", 'ki' => "Q8565476", 'hif' => "Q8562481", 'ast' => "Q1071918", 'id' => "Q155214", 'cv' => "Q58215", 'my' => "Q4614845", 'co' => "Q3111179", 'zea' => "Q2111591", 'fj' => "Q8562502", 'new' => "Q1291627", 'ch' => "Q8576190", 've' => "Q8577029", 'tk' => "Q511754", 'ml' => "Q874555", 'mwl' => "Q8568791", 'nov' => "Q8570353", 'or' => "Q7102897", 'nv' => "Q8569757", 'sq' => "Q208533", 'lv' => "Q728945", 'lb' => "Q950058", 'sh' => "Q58679", 'war' => "Q1648786", 'iu' => "Q3913095", 'so' => "Q8572132", 'mdf' => "Q1178461", 'bm' => "Q8559737", 'kg' => "Q8565463", 'tt' => "Q60819", 'ace' => "Q3957795", 'pih' => "Q8570048", 'hy' => "Q1975217", 'sn' => "Q8571809", 'na' => "Q3753095", 'kab' => "Q8564352", 'lt' => "Q202472", 'zu' => "Q8075204", 'zh-classical' => "Q1378484", 'sr' => "Q200386", 'hi' => "Q722040", 'mzn' => "Q3568048", 'sco' => "Q1444686", 'nap' => "Q1047851", 'bs' => "Q1047829", 'la' => "Q12237", 'szl' => "Q940309", 'za' => "Q3311132", 'nn' => "Q2349453", 'ms' => "Q845993", 'da' => "Q181163", 'min' => "Q4296423", 'wuu' => "Q1110233", 'eml' => "Q3568066", 'xh' => "Q3568065", 'hak' => "Q6112922", 'pam' => "Q588620", 'ty' => "Q3568061", 'srn' => "Q3568060", 'et' => "Q200060", 'nds' => "Q4925786", 'he' => "Q199913", 'ku' => "Q1154741", 'rn' => "Q8565742", 'nso' => "Q13230970", 'dsb' => "Q8561147", 'kv' => "Q925661", 'se' => "Q4115441", 'zh' => "Q30239", 'gan' => "Q6125437", 'io' => "Q1154766", 'fy' => "Q2602203", 'arc' => "Q8569951", 'pa' => "Q1754193", 'gu' => "Q3180306", 'gag' => "Q79633", 'pnt' => "Q4372058", 'pl' => "Q1551807", 'vec' => "Q1055841", 'ik' => "Q8563863", 'as' => "Q8559119", 'ay' => "Q3826575", 'lmo' => "Q3913160", 'ko' => "Q17985", 'sg' => "Q8571487", 'pap' => "Q3568056", 'ps' => "Q3568054", 'ga' => "Q875631", 'sc' => "Q3568059", 'yi' => "Q1968379", 'ny' => "Q8561552", 'bxr' => "Q8561415", 'rue' => "Q58781", 'sah' => "Q225594", 'mrj' => "Q1034940", 'ilo' => "Q8563685", 'chr' => "Q8576237", 'krc' => "Q1249553", 'map-bms' => "Q4077512", 'ur' => "Q1067878", 'si' => "Q8571954", '' => "Q2029239", 'th' => "Q565074", 'tg' => "Q2742472", 'cbk-zam' => "Q8575930", 'nrm' => "Q3568051", 'ee' => "Q8562097", 'fr' => "Q8447", 'bn' => "Q427715", 'bug' => "Q4097773", 'gl' => "Q841208", 'be-x-old' => "Q8937989", 'chy' => "Q8561491", 'es' => "Q8449", 'bar' => "Q1961887", 'lo' => "Q3568045", 'bh' => "Q8561277", 'lij' => "Q3568046", 'ksh' => "Q3568041", 'stq' => "Q3568040", 'haw' => "Q3568043", 'kl' => "Q3568042", 'kaa' => "Q79636", 'bjn' => "Q2983979", 'hu' => "Q53464", 'mo' => "Q3568049", 'eu' => "Q207260", 'am' => "Q3025527", 'li' => "Q2328409", 'ca' => "Q199693", 'km' => "Q3568044", 'udm' => "Q221444", 'cu' => "Q547271", 'rw' => "Q8565518", 'uk' => "Q199698", '$ka' => "Q848974" );
	if ( array_key_exists( $lang, $sources ) ) {
		return $sources[$lang];
	}
	return null;
}