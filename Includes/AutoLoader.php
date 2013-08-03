<?php

namespace Addframe;

/**
 * @since 0.0.1
 * @author Addshore
 **/

class AutoLoader {

	static public $classNames = array();

	/**
	 * Store the filename (sans extension) & full path of all ".php" files found
	 */
	public static function registerDirectory($dirName, $namespace = 'Addframe') {

		$di = new \DirectoryIterator($dirName);
		foreach ($di as $file) {

			if ($file->isDir() && !$file->isLink() && !$file->isDot()) {
				self::registerDirectory($file->getPathname(), $namespace.'\\'.$file->getFilename());

			} elseif ( substr($file->getFilename(), -4) === '.php' ) {

				$className = substr($file->getFilename(), 0, -4);

				//dont bother loading tests
				if( !( substr($className, -4) == "Test" ) ){
					AutoLoader::registerClass($namespace.'\\'.$className, $file->getPathname());
				}
			}
		}
	}

	public static function registerClass($className, $fileName) {
		AutoLoader::$classNames[$className] = $fileName;
	}

	public static function loadClass($className) {
		if (isset(AutoLoader::$classNames[$className])) {
			require_once(AutoLoader::$classNames[$className]);
			return true;
		}
		return false;
	}

}

spl_autoload_register(array('Addframe\AutoLoader', 'loadClass'));