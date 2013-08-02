<?php
/**
 * This file is the master script used for moving information
 * from projects to wikidata
 * @author Addshore
 *
 **/

use Addframe\Config;
use Addframe\Mysql;
use Addframe\Stathat;

require_once( dirname( __FILE__ ) . '/../../../Init.php' );

$db = new Mysql(
	Config::get( 'mysql', 'server'), '3306',
	Config::get( 'mysql', 'user'),
	Config::get( 'mysql', 'password'),
	Config::get( 'mysql', 'user').'_wikidata_p' );

$stathat = new Stathat( Config::get( 'stathat', 'key') );

$redis = new Redis();
$redis->connect(Config::get( 'redis', 'server'));
$redis->setOption(Redis::OPT_PREFIX, Config::get( 'redis', 'prefix'));
$redis->select(9);
$redis->delete(Config::get( 'redis', 'key'));

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
			$redis->lpush(Config::get( 'redis', 'key'), json_encode( $row ) );
		}

		$dbQuery = $db->select( 'iwlink','count(*)', null, null );
		$rows = $db->mysql2array( $dbQuery );
		$stathat->stathat_ez_count( "Addbot - IW Removal - Remaining", $rows[0]['count(*)'] );

		while ( $count > 0){
			echo "Waiting before we add more, $count in list\n";
			$count = $redis->lSize(Config::get( 'redis', 'key'));
			sleep(1);
		}
	}

}
