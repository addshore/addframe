<?php

/*
*** The registry class
*/

class Registry {

	/*
	 * The $objects variable will contain all the stuff
	 */
	var $objects = array();

	/*
	 * The __constructor method will run when the class is first created
	 */
	public function __construct() {
	}

	/*
	 * The __set magic method will be used to add new objects to the $objects
	 */
	public function __set( $index, $value ) {
		$this->objects[$index] = $value;
	}

	/*
	 * The magic method __get will be used when were trying to pull objects from the storage variable
	 */
	public function __get( $index ) {
		if (!isset($this->objects[$index])){
			throw new Exception("Undefined index '$index' in registry");
		}
		return $this->objects[$index];
	}

	/**
	 * @return array of keys currently stored
	 */
	public function getKeys() {
		return array_keys($this->objects);
	}

	/*
	 * These helps save system resources if your Registry gets on the larger side.
	 */
	function __sleep() { /*serialize on sleep*/
		$this->objects = serialize( $this->objects );
	}

	function __wake() { /*un serialize on wake*/
		$this->$objects = unserialize( $this->objects );
	}
}