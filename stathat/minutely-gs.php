<?
//require
require '/data/project/addbot/classes/stathat.php';
require '/data/project/addbot/config/stathat.php';
doChecks();
sleep(30);
doChecks();

function doChecks() 
{ 
global $config;

//Labs Bots OGE Job count
stathat_ez_value($config['stathatkey'], "Wikimedia Labs Bots Job Count (Total)" , intval(exec("qstat -u '*' | grep -c @")));
stathat_ez_value($config['stathatkey'], "Wikimedia Labs Bots Job Count (Running)" , intval(exec("qstat -u '*' | grep -c '   r   '")));

} 


?>