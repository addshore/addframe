<?php

namespace Addframe;

/**
 * Class Mysql
 */
class Mysql {
	/**
	 * MySQL object
	 * @var resource
	 */
	private $mConn;

	/**
	 * Read-only mode
	 * @var bool
	 */
	private $mReadonly;

	private $mHost;
	private $mPort;
	private $mUser;
	private $mPass;
	private $mDb;

	/**
	 * @param $host string Host of server
	 * @param $port string Post of server
	 * @param $user string User for server
	 * @param $pass string Password for user
	 * @param $db string Database to connect to
	 * @param bool $readonly
	 */
	public function __construct( $host, $port = '3306', $user, $pass, $db, $readonly = false ) {
		$this->mHost = $host;
		$this->mPort = $port;
		$this->mUser = $user;
		$this->mPass = $pass;
		$this->mDb = $db;
		$this->mReadonly = $readonly;

		$this->connectToServer();
	}

	/**
	 * @param bool $force
	 */
	private function connectToServer( $force = false ) {
		$this->mConn = mysql_connect( $this->mHost.':'.$this->mPort, $this->mUser, $this->mPass, $force );
		mysql_query("SET character_set_results = 'utf8',".
			" character_set_client = 'utf8', character_set_connection = 'utf8',".
			" character_set_database = 'utf8', character_set_server = 'utf8'", $this->mConn);
		mb_language('uni');
		mb_internal_encoding('UTF-8');
		mysql_select_db( $this->mDb, $this->mConn );
	}

	/**
	 * Destruct function, front-end for mysql_close.
	 * @return void
	 */
	public function __destruct() {
		mysql_close( $this->mConn );
	}

	/**
	 * Front-end for mysql_query. It's preferred to not use this function,
	 * and rather the other Database::select, update, insert, and delete functions.
	 * @param string $sql Raw DB query
	 * @return object|bool MySQL object, false if there's no result
	 */
	public function doQuery( $sql ) {
		$sql = trim( $sql );
		//echo "MySQL: $sql;\n";
		$result = mysql_query( $sql, $this->mConn );
		//var_dump($result);
		if ( mysql_errno( $this->mConn ) == 2006 ) {
			$this->connectToServer( true );
			$result = mysql_query( $sql, $this->mConn );
		}

		if( $this->errorStr() ){
			echo $this->errorStr();
		}

		if ( ! $result )
			return false;
		return $result;
	}

	/**
	 * Front-end for mysql_error
	 * @return string|bool MySQL error string, null if no error
	 */
	public function errorStr() {
		$result = mysql_error( $this->mConn );
		if ( ! $result )
			return false;
		return $result;
	}

	/**
	 * Front-end for mysql_real_escape_string
	 * @param string $data Data to escape
	 * @return string Escaped data
	 */
	public function mysqlEscape( $data ) {
		return mysql_real_escape_string( $data, $this->mConn );
	}

	/**
	 * Shortcut for converting a MySQL result object to a plain array
	 * @param object $data MySQL result
	 * @return array Converted result
	 * @static
	 */
	public static function mysql2array( $data ) {
		if($data === false){
			return false;
		}

		$return = array();
		while ( $row = mysql_fetch_assoc( $data ) ) {
			$return[] = $row;
		}

		return $return;
	}

	/**
	 * SELECT frontend
	 * @param array|string $table Table(s) to select from. If it is an array, the tables will be JOINed.
	 * @param string|array $fields Columns to return
	 * @param string|array $where Conditions for the WHERE part of the query. Default null.
	 * @param array $options Options to add, can be GROUP BY, HAVING, and/or ORDER BY. Default an empty array.
	 * @param array $join_on If selecting from more than one table, this adds an ON statement to the query. Defualt an empty array.
	 * @return object MySQL object
	 */
	public function select( $table, $fields, $where = null, $options = array(), $join_on = array() ) {
		if ( is_array( $fields ) ) {
			$fields = implode( ',', $fields );
		}

		if ( ! is_array( $options ) ) {
			$options = array( $options );
		}

		if ( is_array( $table ) ) {
			if ( count( $join_on ) == 0 ) {
				$from = 'FROM ' . implode( ',', $table );
				$on = null;
			} else {
				$tmp = array_shift( $table );
				$from = 'FROM ' . $tmp;
				$from .= ' JOIN ' . implode( ' JOIN ', $table );

				$tmp = array_keys( $join_on );
				$on = 'ON ' . $tmp[0] . ' = ' . $join_on[$tmp[0]];
			}
		} else {
			$from = 'FROM ' . $table;
			$on = null;
		}

		$newoptions = null;
		if ( isset( $options['GROUP BY'] ) )
			$newoptions .= "GROUP BY {$options['GROUP BY']}";
		if ( isset( $options['HAVING'] ) )
			$newoptions .= "HAVING {$options['HAVING']}";
		if ( isset( $options['ORDER BY'] ) )
			$newoptions .= "ORDER BY {$options['ORDER BY']}";

		if ( ! is_null( $where ) ) {
			if ( is_array( $where ) ) {
				$where_tmp = array();
				foreach ( $where as $wopt ) {
					$tmp = $this->mysqlEscape( $wopt[2] );
					if ( $wopt[1] == 'LIKE' )
						$tmp = $wopt[2];
					$where_tmp[] = '`' . $wopt[0] . '` ' . $wopt[1] . ' \'' . $tmp . '\'';
				}
				$where = implode( ' AND ', $where_tmp );
			}
			$sql = "SELECT $fields $from $on WHERE $where $newoptions";
		} else {
			$sql = "SELECT $fields $from $on $newoptions";
		}

		if ( isset( $options['LIMIT'] ) ) {
			$sql .= " LIMIT {$options['LIMIT']}";
		}

		if ( isset( $options['EXPLAIN'] ) ) {
			$sql = 'EXPLAIN ' . $sql;
		}

		//echo $sql;
		return $this->doQuery( $sql );
	}

	/**
	 * INSERT frontend
	 * @param $table string Table to insert into.
	 * @param $values array Values to set.
	 * @param $options array Options
	 * @return object MySQL object
	 * @throws \Exception Write query called while under read-only mode
	 */
	public function insert( $table, $values, $options = array() ) {
		//echo "Running insert.";
		if ( $this->mReadonly == true )
			throw new \Exception( "Write query called while under read-only mode" );
		if ( ! count( $values ) ) {
			return true;
		}

		if ( ! is_array( $options ) ) {
			$options = array( $options );
		}

		$cols = array();
		$vals = array();
		foreach ( $values as $col => $value ) {
			$cols[] = "`$col`";
			$vals[] = "'" . $this->mysqlEscape( $value ) . "'";
		}

		$cols = implode( ',', $cols );
		$vals = implode( ',', $vals );

		$sql = "INSERT " . implode( ' ', $options ) . " INTO $table ($cols) VALUES ($vals)";
		//echo $sql;
		return (bool)$this->doQuery( $sql );
	}

	/**
	 * UPDATE frontend
	 * @param $table string Table to update.
	 * @param $values array Values to set.
	 * @param $conds string|array Conditions to update. Default *, updates every entry.
	 * @return object MySQL object
	 * @throws \Exception Write query called while under read-only mode
	 */
	public function update( $table, $values, $conds = '*' ) {
		if ( $this->mReadonly == true )
			throw new \Exception( "Write query called while under read-only mode" );
		$vals = array();
		foreach ( $values as $col => $val ) {
			$vals[] = "`$col`" . "= '" . $this->mysqlEscape( $val ) . "'";
		}
		$vals = implode( ', ', $vals );

		$sql = "UPDATE $table SET " . $vals;
		if ( $conds != '*' ) {
			$cnds = array();
			foreach ( $conds as $col => $val ) {
				$cnds[] = "`$col`" . "= '" . $this->mysqlEscape( $val ) . "'";
			}
			$cnds = implode( ' AND ', $cnds );

			$sql .= " WHERE " . $cnds;
		}
		return $this->doQuery( $sql );
	}

	/**
	 * DELETE frontend
	 * @param $table string Table to delete from.
	 * @param $conds array Conditions to delete. Default *, deletes every entry.
	 * @return object MySQL object
	 * @throws \Exception Write query called while under read-only mode
	 */
	public function delete( $table, $conds ) {
		if ( $this->mReadonly == true )
			throw new \Exception( "Write query called while under read-only mode" );
		$sql = "DELETE FROM $table";
		if ( $conds != '*' ) {
			$cnds = array();
			foreach ( $conds as $col => $val ) {
				$cnds[] = "`$col`" . "= '" . $this->mysqlEscape( $val ) . "'";
			}
			$cnds = implode( ' AND ', $cnds );

			$sql .= " WHERE " . $cnds;
		}
		return $this->doQuery( $sql );
	}
}