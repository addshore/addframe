<?php

namespace Addframe\Tests;
use Addframe\Mysql;


/**
 * @covers Addframe\Mysql
 *
 * @since 0.0.2
 *
 * @author Addshore
 */

class MysqlTest extends \PHPUnit_Framework_TestCase {

	//@todo we should actually connect to and test a database!
	//@todo then test everything else

	function provideMysqlDetails(){
		return array(
			array( 'localhost', '3306','username','password','database', false ),
			array( '127.0.0.1', null,'username','password','database', false ),
			array( 'localhost', 3306,'username','PasSW046','Database_Name_', false ),
		);
	}

	/**
	 * @dataProvider provideMysqlDetails
	 */
	function testCanConstruct( $host, $port, $username, $password, $database, $connect ){
		new Mysql( $host, $port, $username, $password, $database, $connect );
		$this->assertTrue( true );
	}

}