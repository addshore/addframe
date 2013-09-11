<?php

namespace Addframe;

use Exception;

/**
 * Class for basic caching
 *
 * This allows caching of data from Cachable objects
 * the data is identified by a hash from the object
 **/

class Cache {

	/**
	 * @var string prefix of the cache files created
	 */
	static $prefix = 'c_';

	/**
	 * @param Cacheable $item
	 * @throws CacheException
	 */
	public static function add( Cacheable $item ){
		$path = self::getPath( $item );
		$addResult = file_put_contents( $path, json_encode( $item->getCacheData() ),LOCK_EX );
		if( $addResult === false ){
			throw new CacheException( "Failed to write cache item with name '{$path}'" );
		}
	}

	/**
	 * @param Cacheable $item
	 * @return mixed|null
	 * @throws CacheException
	 */
	public static function get( Cacheable $item ){
		if( self::has( $item ) ){
			$path = self::getPath( $item );
			$getResult = file_get_contents( $path );
			if( $getResult === false ){
				throw new CacheException( "Failed to get cache item with name '{$path}'" );
			}
			Logger::logDebug( "CACHE get {$path}" );
			return json_decode( $getResult, true );
		}
		return null;
	}

	/**
	 * @param Cacheable $item
	 * @throws CacheException
	 */
	public static function remove( Cacheable $item ){
		if( self::has( $item ) ){
			$path = self::getPath( $item );
			$deleteResult = unlink( $path );
			if( $deleteResult === false ){
				throw new CacheException( "Failed to delete cache item with name '{$path}'" );
			}
		}
	}

	/**
	 * @param Cacheable $item
	 * @return bool
	 */
	public static function has( Cacheable $item ){
		if( file_exists( self::getPath( $item ) ) ){
			return true;
		} else {
			return false;
		}
	}

	/**
	 * @param Cacheable $item
	 * @return int|null
	 * @throws CacheException
	 */
	public static function age( Cacheable $item ){
		$age = null;
		if( self::has( $item ) ){
			$path =  self::getPath( $item );
			$cacheTime = filemtime ( $path );
			if( $cacheTime === false ){
				throw new CacheException( "Failed to get age of cache item with name '{$path}'" );
			}
			$currentTime = time();
			$age = $currentTime - $cacheTime;
		}
		return $age;
	}

	/**
	 * @param Cacheable $item
	 * @return string Path to the cache file for the item
	 */
	protected static function getPath( Cacheable $item ){
		return __DIR__ . '/../cache/' . self::$prefix.$item->getHash();
	}

	/**
	 * Removes all current cache files
	 * @throws CacheException
	 */
	public static function clear(){
		try{
			array_map('unlink', glob( __DIR__.'/../cache/'.self::$prefix.'*' ) );
		} catch( Exception $e ){
			throw new CacheException( "Failed to clear cache", 0, $e );
		}

	}

}

/**
 * Class CacheException
 */
class CacheException extends Exception {

}