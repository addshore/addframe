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
	 * All configs will be loaded into the static Config class
	 * They can then be accessed through this class
	 *
	 */
	public static function loadConfigs( $configPath ){
		$di = new \DirectoryIterator( $configPath );

		// First load the defaults
		foreach ($di as $file) {

			if ($file->isDir() && !$file->isLink() && !$file->isDot()) {
				//do nothing
			} elseif (substr($file->getFilename(), -4) === '.cnf') {
				$configName = substr($file->getFilename(), 0, -4);
				Config::$settings[$configName] = parse_ini_file( $configPath.'/'.$file->getFilename() );
			}
		}

		//Then load the private configs
		foreach ($di as $file) {
			if ($file->isDir() && !$file->isLink() && !$file->isDot()) {
				//do nothing
			} elseif (substr($file->getFilename(), -5) === '.cnfp') {
				$configName = substr($file->getFilename(), 0, -5);
				Config::$settings[$configName] = parse_ini_file( $configPath.'/'.$file->getFilename() );
			}
		}

	}

	public static function set($config, $setting){
		Config::$settings[$config] = $setting;
	}

	public static function setFromIni($config, $location){
		Config::set( $config, parse_ini_file( $location ) );
	}

	public static function get( $config, $setting = null ){
		if( $setting == null ){
			if( isset( Config::$settings[$config] ) ) {
				return Config::$settings[$config];
			}
		}
		if( isset( Config::$settings[$config][$setting] ) ) {
			return Config::$settings[$config][$setting];
		}
		return null;
	}


}