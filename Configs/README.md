Configuration files
-------------
All of the configs in this file will be loaded when Init.php is run

First all configs ending in .cfg will be loading (we presume these are the defaults)
Then all configs ending in .cfgp will be loaded (these are user specific)

All configs will be loaded into the static Config class.
They will be accessible via the below
Config::get('configname excluding .cfgp?', 'setting') = value;
