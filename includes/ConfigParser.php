<?php

namespace Addframe;

/**
 * Configuration INI files will be created in this order
 * DEFAULT Section values assigned
 * If HTTP_HOST contains EXTENDS value, get the values for that section
 * Then read the values for the specific HTTP_HOST
 *
 * USAGE:
 * $a = new configParser();
 * $b = $a->get(); // or $a->get('keyNameHere')
 * $b['KEY]
 *
 */
class ConfigParser {
	private $config = array();

	function __construct($iniFile="config.ini"){
		// read in the configuration file
		$config = parse_ini_file($iniFile, true);
		$host = strtolower($_SERVER['HTTP_HOST']);

		// default specific
		if( array_key_exists("default",$config) ){
			$this->config = array_merge($this->config,$config["default"]);
		}

		// recursively put together the configuration environment (precedence = this host, order of extended configurations, default--if extended multiple times, highest level takes precedence)
		$this->config = $this->loadEnvironment(strtolower($_SERVER['HTTP_HOST']), $config) + $this->loadEnvironment("default", $config);
	}

	private function underscore2Camelcase($str) {
		// Split string in words.
		$words = explode('_', strtolower($str));

		$return = '';
		foreach ($words as $word) {
			$return .= ucfirst(trim($word));
		}

		return $return;
	}

	// recursive assign, returns configuration for the specified environment
	private function loadEnvironment($environment,$config, &$path=array()){
		// prevent case where a->b and b->a (infinite loop)
		if( in_array($environment, $path) ){
			// item already parsed, return from the function
			return array();
		} else {
			// add to path and continue
			array_push($path, $environment);
		}

		if( array_key_exists("EXTENDS",$config[$environment]) ){
			// append the configuration information of this environment with the extends environment
			return $config[$environment] + $this->loadEnvironment($config[$environment]["EXTENDS"],$config,$path);
		} else {
			// return the configuration for the current environment
			return $config[$environment];
		}
	}

	// retrieves a single item or the entire config array
	public function get($key=""){
		if($key == ""){
			return $this->config;
		} else {
			return $this->config[strtoupper($key)];
		}
	}

	// converts specified values into javascript (called on document ready)
	public function toJS($keys=array()){
		// non-array type returns an error
		if(!is_array($keys)){
			throw new Exception('keys must be an array');
		}

		// empty array gets an empty string
		if( count($keys) == 0 ) return "";


		$jsValues = "";
		foreach( $keys as $i ){
			$jsValues .= sprintf("%s = '%s'; ", $this->underscore2Camelcase($i), $this->get($i));
		}

		return sprintf('<script type="text/javascript">$(function(){%s});</script>',$jsValues) . "\n";
	}
}