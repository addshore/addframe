<?php


namespace Addframe\Mediawiki;


class ResultCache {

	public static function addResult( ApiRequest $request ){
		$hash = $request->getHash();
		$result = $request->getResult();
		file_put_contents( self::getPathForHash( $hash ), json_encode( $result ) );
		return true;
	}

	public static function getResultWithHash( $hash ){
		if( self::hasResultWithHash( $hash ) ){
			return json_decode( file_get_contents( self::getPathForHash( $hash ) ), true );
		}
		return null;
	}

	public static function removeResultWithHash( $hash ){
		if( self::hasResultWithHash( $hash ) ){
			unlink( self::getPathForHash( $hash ) );
			return true;
		}
		return false;
	}

	public static function hasResultWithHash( $hash ){
		if( file_exists( self::getPathForHash( $hash ) ) ){
			return true;
		} else {
			return false;
		}
	}

	protected static function getPathForHash( $hash ){
		return __DIR__.'/../../cache/result_'.$hash;
	}

	public static function clearCachedResults(){
		array_map('unlink', glob( self::getPathForHash( '*' ) ) );
	}

}