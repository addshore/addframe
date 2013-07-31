<?php

namespace Addframe;

/**
 * Class IrcServer
 */

class IrcServer  {

	private $socket;
	private $host;
	private $port;
	private $username;
	private $readBuffer = array();

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
				$parts = explode(' ',$read);

				if (strtolower($parts[0]) == 'ping') {
					$this->write("PONG :".substr($read, 6)); //Reply with pong
				} elseif (strtolower($parts[1]) == 'privmsg') {
					$this->addToReadBuffer( $read );
				}
			}
		}
	}

	private function addToReadBuffer( $what ) {
		$this->readBuffer[] = $what;
	}

	public function flushReadBuffer(){
		$this->readBuffer = array();
	}

	public function write( $what ) {
		@fwrite($this->socket, $what."\r");
	}

	public function read(){
		return array_shift( $this->readBuffer );
	}

	public function joinChannel( $channel ){
		$this->write("JOIN $channel");
	}


}