<?
//require
require '../classes/stathat.php';
require '../config/stathat.php';
doChecks();
sleep(30);
doChecks();

function doChecks() 
{ 
global $config;

//Labs Bots OGE Job count
stathat_ez_value($config['stathatkey'], "Wikimedia Labs Bots Job Count (Total)" , intval(exec("qstat -u '*' | grep addshore | grep -c @")));

} 


?>