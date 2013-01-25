<?
require 'classes/database.php';
require 'config.php';

echo "Connecting to DB...\n";
$db = new Database( $config['dbhost'], $config['dbport'], $config['dbuser'], $config['dbpass'], $config['dbname'], false);

// remove checked articles older than 24 hours
$res = $db->doQuery('DELETE FROM checked WHERE checked < TIMESTAMPADD(DAY,-1,NOW())');

?>