<?
//require
require __DIR__.'/../../classes/stathat.php';
require __DIR__.'/../../config/stathat.cfg';
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