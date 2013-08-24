<?php

namespace Addframe;

class Cache {

	static $prefix = 'c_';

	public static function add( Cacheable $item ){
		file_put_contents( self::getPath( $item ), json_encode( $item->getCacheData() ) );
		return true;
	}

	public static function get( Cacheable $item ){
		if( self::has( $item ) ){
			return json_decode( file_get_contents( self::getPath( $item ) ), true );
		}
		return null;
	}

	public static function remove( Cacheable $item ){
		if( self::has( $item ) ){
			unlink( self::getPath( $item ) );
			return true;
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
			$cacheTime = filectime ( self::getPath( $item ) );
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