<?php
/**
 * This file is the main script used for moving information
 * from projects to wikidata
 * @author Addshore
 *
 * This file expects a mysql table in the format below..
 *
 * CREATE TABLE `iwlink` (
 * `site` char(10) NOT NULL,
 * `lang` char(12) NOT NULL,
 * `namespace` smallint(6) NOT NULL,
 * `title` char(200) NOT NULL,
 * `links` smallint(6) DEFAULT NULL,
 * `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 * `log` char(250) DEFAULT NULL,
 * PRIMARY KEY (`site`,`lang`,`namespace`,`title`)
 * ) ENGINE=InnoDB DEFAULT CHARSET=utf8
 *
 **/

use Addframe\Globals;
use Addframe\Mysql;
use Addframe\Stathat;

require_once( dirname( __FILE__ ) . '/../../init.php' );

$db = new Mysql(
	Globals::$config['mysql']['server'], '3306',
	Globals::$config['mysql']['user'],
	Globals::$config['mysql']['password'],
	Globals::$config['mysql']['user'].'_wikidata_p' );

$stathat = new Stathat( Globals::$config['stathat']['key'] );

$redis = new Redis();
$redis->connect(Globals::$config['redis']['server']);
$redis->setOption(Redis::OPT_PREFIX, Globals::$config['redis']['prefix']);
$redis->select(9);
$redis->delete('iwlink');

$count = 0;

while(true){

	$group = $db->mysql2array( $db->doQuery("SELECT lang,site from iwlink group by lang,site order by updated ASC") );

	foreach( $group as $grp ){

		//no flags for these wikis...
		if($grp['site'] == 'wikivoyage'){
			$badLangs = array('fr','sv','ro','he','el');
			foreach( $badLangs as $badLang ){
				if( $grp['lang'] == $badLang ){
					continue 2;
				}
			}
		}

		echo "Querying db\n";
		$dbQuery = $db->select( 'iwlink','*', "site = '".$grp['site']."' AND lang = '".$grp['lang']."'", array('ORDER BY' => 'updated ASC' ) );
		$rows = $db->mysql2array( $dbQuery );
		if( $rows === false ){
			die('Empty database?');
		}

		echo "Adding to redis for site = '".$grp['site']."' AND lang = '".$grp['lang']."'\n";
		foreach( $rows as $row ){
			$count++;
			$redis->lpush(Globals::$config['redis']['key'], json_encode( $row ) );
		}

		$dbQuery = $db->select( 'iwlink','count(*)', null, null );
		$rows = $db->mysql2array( $dbQuery );
		$stathat->stathat_ez_count( "Addbot - IW Removal - Remaining", $rows[0]['count(*)'] );

		while ( $count > 0){
			echo "Waiting before we add more, $count in list\n";
			$count = $redis->lSize(Globals::$config['redis']['key']);
			sleep(1);
		}
	}

}
