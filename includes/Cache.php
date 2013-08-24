<?php

namespace Addframe;

class Cache {

	static $prefix = 'c_';

	public static function add( Cacheable $item ){
		$path = self::getPath( $item );
		$addResult = file_put_contents( $path, json_encode( $item->getCacheData() ) );
		if( $addResult === false ){
			throw new \IOException( "Failed to write cache item with name '{$path}'" );
		}
		return true;
	}

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

	public static function remove( Cacheable $item ){
		if( self::has( $item ) ){
			$path = self::getPath( $item );
			$deleteResult =  unlink( $path );
			if( $deleteResult === false ){
				throw new \IOException( "Failed to delete cache item with name '{$path}'" );
			}
			return $deleteResult;
		}
		return false;
	}

	public static function has( Cacheable $item ){
		if( file_exists( self::getPath( $item ) ) ){
			return true;
		} else {
			return false;
		}
	}

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

	protected static function getPath( Cacheable $item ){
		return __DIR__.'/../cache/'.self::$prefix.$item->getHash();
	}

	public static function clear(){
		array_map('unlink', glob( __DIR__.'/../cache/'.self::$prefix.'*' ) );
	}

}