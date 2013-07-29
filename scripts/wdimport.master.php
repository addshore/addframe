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

require_once( dirname( __FILE__ ) . '/../init.php' );

$db = new Mysql(
	'tools-db', '3306',
	Globals::$config['replica.my']['user'],
	Globals::$config['replica.my']['password'],
	Globals::$config['replica.my']['user'].'_wikidata_p' );

$redis = new Redis();
$redis->connect('tools-mc');
$redis->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_PHP);
$redis->setOption(Redis::OPT_PREFIX, 'addbotiw:');
$redis->select(9);

while(true){

	$dbQuery = $db->select( 'iwlink','*', null, array('ORDER BY' => 'updated ASC', 'LIMIT' => '50' ) );
	$rows = $db->mysql2array( $dbQuery );
	if( $rows === false ){
		die('Empty database?');
	}

	foreach( $rows as $row ){
		$redis->lpush('iwlink', serialize( $row ) );
	}

	while ( $redis->lSize('iwlink') > 0){
		sleep(1);
	}

}
