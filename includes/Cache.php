<?php

namespace Addframe;

class Cache {

	public static function add( Cacheable $item ){
		$hash = $item->getHash();
		$data = $item->getCacheData();
		file_put_contents( self::getPath( $hash ), json_encode( $data ) );
		return true;
	}

	public static function get( $hash ){
		if( self::has( $hash ) ){
			return json_decode( file_get_contents( self::getPath( $hash ) ), true );
		}
		return null;
	}

	public static function remove( $hash ){
		if( self::has( $hash ) ){
			unlink( self::getPath( $hash ) );
			return true;
		}
		return false;
	}

	public static function has( $hash ){
		if( file_exists( self::getPath( $hash ) ) ){
			return true;
		} else {
			return false;
		}
	}

	protected static function getPath( $hash ){
		return __DIR__.'/../cache/c_'.$hash;
	}

	public static function clear(){
		array_map('unlink', glob( self::getPath( '*' ) ) );
	}

}