<?

// main settings
$config['url'] = 'en.wikipedia.org';                        // wiki url we will be working on
$config['user'] = 'Addbot';                                 // bot username for login
$config['owner'] = 'Addshore';                              // bot owner
$config['sandbox'] = 'User:'.$config['user'].'/Sandbox';    // sandbox location
require '/home/addshore/.password.addbot';                  // $config['password'] = 'password';

// database settings
$config['dbhost'] = '';
$config['dbport'] = '';
$config['dbuser'] = '';
require '/home/addshore/.password.db';            //$config['dbpass'] = 'password';
$config['dbname'] = '';

// table settings
$config['tblist'] = 'ab_tocheck';                 // table containing articles to be checked
$config['checked'] = 'ab_checked';                // table containing articles checked along with time

?>