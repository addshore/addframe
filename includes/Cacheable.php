<?php

namespace Addframe;

interface Cacheable {

	/**
	* Returns a hash based on the value of the object.
	*
	* @return string
	*/
	public function getHash();

	/**
	 * Returns data to be cached
	 *
	 * @return mixed
	 */
	public function getCacheData();

}