<?php

namespace Addframe;

/**
 * Class to handle all configs
 *
 * @since 0.0.4
 * @author Addshore
 **/

class Config {

	private static $settings;

	/**
	 * This function loads all configs in /Configs
	 *
	 * First all configs ending in .cfg will be loading (we presume these are the defaults)
	 * Then all configs ending in .cfgp will be loaded (these are user specific)
	 *
	 * All configs will be loaded into Globals::$config in the format...
	 * Globals::$config['configname excluding .cfgp?']['setting'] = value;
	 *
	 */
	public static function loadConfigs(){
		// Specify the config directory
		$configPath = dirname( __FILE__ ).'/../Configs';
		$di = new \DirectoryIterator($configPath);

		// First load the defaults
		foreach ($di as $file) {

			if ($file->isDir() && !$file->isLink() && !$file->isDot()) {
				//do nothing
			} elseif (substr($file->getFilename(), -4) === '.cnf') {
				$configName = substr($file->getFilename(), 0, -4);
				$settings[$configName] = parse_ini_file( $configPath.'/'.$file->getFilename() );
			}
		}

		//Then load the private configs
		foreach ($di as $file) {
			if ($file->isDir() && !$file->isLink() && !$file->isDot()) {
				//do nothing
			} elseif (substr($file->getFilename(), -5) === '.cnfp') {
				$configName = substr($file->getFilename(), 0, -5);
				$settings[$configName] = parse_ini_file( $configPath.'/'.$file->getFilename() );
			}
		}

	}

	public static function get( $config, $setting ){
		if( isset( Config::$settings[$config][$setting] ) ) {
			return Config::$settings[$config][$setting];
		}
		return null;
	}


}