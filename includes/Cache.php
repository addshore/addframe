<?php

namespace Addframe;

/**
 * Class for basic caching of objects in files
 **/

class Cache {

	/**
	 * @var string prefix of the cache files created
	 */
	static $prefix = 'c_';

	/**
	 * @param Cacheable $item
	 * @throws \IOException
	 */
	public static function add( Cacheable $item ){
		$path = self::getPath( $item );
		$addResult = file_put_contents( $path, json_encode( $item->getCacheData() ) );
		if( $addResult === false ){
			throw new \IOException( "Failed to write cache item with name '{$path}'" );
		}
	}

	/**
	 * @param Cacheable $item
	 * @return mixed|null
	 * @throws \IOException
	 */
	public static function get( Cacheable $item ){
		if( self::has( $item ) ){
			$path = self::getPath( $item );
			$getResult = file_get_contents( $path );
			if( $getResult === false ){
				throw new \IOException( "Failed to get cache item with name '{$path}'" );
			}
			return json_decode( $getResult, true );
		}
		return null;
	}

	/**
	 * @param Cacheable $item
	 * @throws \IOException
	 */
	public static function remove( Cacheable $item ){
		if( self::has( $item ) ){
			$path = self::getPath( $item );
			$deleteResult = unlink( $path );
			if( $deleteResult === false ){
				throw new \IOException( "Failed to delete cache item with name '{$path}'" );
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
	 * @throws \IOException
	 */
	public static function age( Cacheable $item ){
		$age = null;
		if( self::has( $item ) ){
			$path =  self::getPath( $item );
			$cacheTime = filemtime ( $path );
			if( $cacheTime === false ){
				throw new \IOException( "Failed to get age of cache item with name '{$path}'" );
			}
			$currentTime = time();
			$age = $currentTime - $cacheTime;
		}
		return $age;
	}

	/**
	 * @param Cacheable $item
	 * @return string path to the cache file for the item
	 */
	protected static function getPath( Cacheable $item ){
		return __DIR__.'/../cache/'.self::$prefix.$item->getHash();
	}

	/**
	 * Removes all current cache files
	 */
	public static function clear(){
		array_map('unlink', glob( __DIR__.'/../cache/'.self::$prefix.'*' ) );
	}

}