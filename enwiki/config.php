<?
global $config;

// main settings
$config['url'] = 'en.wikipedia.org';                        // wiki url we will be working on
$config['user'] = 'Addbot';                                 // bot username for login
$config['owner'] = 'Addshore';                              // bot owner
$config['mysandbox'] = 'User:'.$config['user'].'/Sandbox';    // sandbox location
$config['debug'] = false;									// true for debugging
require '/home/addshore/.password.addbot';                  // $config['password'] = 'password';

// database settings
$config['dbhost'] = 'i-000000b4.pmtpa.wmflabs';
$config['dbport'] = '3306';
$config['dbuser'] = 'addshore';
require '/home/addshore/.password.db';            //$config['dbpass'] = 'password';
$config['dbname'] = 'addbot';

// table settings
$config['tblist'] = 'pending';                 // table containing articles to be checked
$config['tbdone'] = 'checked';                // table containing articles checked along with time

?>
