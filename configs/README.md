All of the configs in this file will be loaded when init.php is run

First all configs ending in .cfg will be loading (we presume these are the defaults)
Then all configs ending in .cfgp will be loaded (these are user specific)

All configs will be loaded into Globals::$config in the format...
Globals::$config['configname excluding .cfgp?']['setting'] = value;