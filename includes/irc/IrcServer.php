<?php

namespace Addframe;

/**
 * Class IrcServer
 *
 * @since 0.0.2
 * @author Addshore
 */

class IrcServer  {

	private $socket;
	private $host;
	private $port;
	private $username;

	function __construct( $host, $username, $port = 6667, $connect = true ) {
		$this->host = $host;
		$this->port = $port;
		$this->username = $username;
		if( $connect === true ){
			$this->connect();
		}
	}

	private function connect() {
		$this->socket = @fsockopen($this->host, $this->port, $errno, $errstr, 10);
		if( $this->socket ){
			$this->write( "NICK ".$this->username );
			$this->write( "USER ".$this->username );

			while( !feof ( $this->socket ) ){
				$read = str_replace(array("\n","\r"),'',fgets($this->socket, 1024)); //get a line of data from the server
				//todo, if threads are enabled we can fork one for Ping and Pong here
			}
		}
	}

	public function write( $what ) {
		@fwrite($this->socket, $what."\r");
	}

	public function read(){
		$read = str_replace(array("\n","\r"),'',fgets($this->socket, 1024));
		$parts = explode(' ',$read);
		if (strtolower($parts[0]) == 'ping') {
			$this->pong( $read );
		} elseif (strtolower($parts[1]) == 'privmsg') {
			return $read;
		}
		return $this->read();
	}

	public function pong( $read ){
		//todo, does this actually need $read?
		$this->write("PONG :".substr($read, 6));
	}

	public function isConnected(){
		return !feof ( $this->socket );
	}

	public function joinChannel( $channel ){
		$this->write("JOIN $channel");
	}

	//todo changename

}