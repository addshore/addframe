<?php

namespace Addframe;

/**
 * Configuration INI files will be created in this order
 * 1 => DEFAULT Section values assigned
 * 2 => If $environment contains EXTENDS value, get the values for that section and assign
 * 3 => Then read the values for the specific $environment and assign
 *
 * @USAGE:
 * $a = new configParser( $environment );
 * $b = $a->get(); // or $a->get('keyNameHere')
 * $b['KEY]
 *
 * @original https://github.com/ejhayes/php-config/blob/master/config.php
 * @author https://github.com/ejhayes
 *
 */
class ConfigParser {

	/**
	 * @var array of the actual config that is loaded
	 */
	private $config = array();

	/**
	 * @param string $environment given environment to load
	 */
	function __construct( $environment = "default" ) {

		$configDefault = parse_ini_file( __DIR__ . '/../LocalSettings.default.ini', true );
		$config = parse_ini_file( __DIR__ . '/../LocalSettings.ini', true );
		$config = array_merge( $configDefault, $config );

		// default specific
		if( array_key_exists( "default",$config ) ){
			$this->config = array_merge( $this->config,$config["default"] );
		}

		// recursively put together the configuration environment (precedence = this host, order of extended configurations,
		// default--if extended multiple times, highest level takes precedence)
		$this->config = $this->loadEnvironment( strtolower( $environment ), $config ) + $this->loadEnvironment( "default", $config );
	}

	/**
	 * Recursive assign, returns configuration for the specified environment
	 * @param $environment
	 * @param $config
	 * @param array $path
	 * @throws \Exception
	 * @return array
	 */
	private function loadEnvironment( $environment, $config, &$path=array() ) {
		// prevent case where a->b and b->a (infinite loop)
		if( in_array( $environment, $path ) ){
			// item already parsed, return from the function
			return array();
		} else {
			// add to path and continue
			array_push( $path, $environment );
		}

		if( !array_key_exists( $environment, $config ) ){
			throw new \Exception( "No such environment {$environment} defined in LocalSettings" );
		}

		if( array_key_exists( "EXTENDS", $config[$environment] ) ) {
			// append the configuration information of this environment with the extends environment
			$weExtend = $config[ $environment ]["EXTENDS"];
			unset( $config[ $environment ]["EXTENDS"] );
			return $config[ $environment ] + $this->loadEnvironment( $weExtend, $config, $path );
		} else {
			// return the configuration for the current environment
			return $config[ $environment ];
		}
	}

	/**
	 * Retrieves a single item or the entire config array
	 * @param string $key
	 * @return mixed
	 */
	public function get( $key="" ) {
		if( $key == "" ){
			return $this->config;
		} else {
			return $this->config[ $key ];
		}
	}
}