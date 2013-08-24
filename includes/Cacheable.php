<?php

namespace Addframe;

/**
 * Interface for Cacheable objects
 **/

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

	/**
	 * Returns the maximum time to cache the data for in seconds
	 *
	 * @return int minutes
	 */
	public function maxCacheAge();

}