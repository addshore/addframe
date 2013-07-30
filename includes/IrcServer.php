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
		$pid = pcntl_fork();
		if( $pid == 0 ){
			set_time_limit(0);

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
	}

	private function addToReadBuffer( $what ) {
		//@todo add this to the queue of incomming lines
	}

	public function flushReadBuffer(){
		//@todo flash the read buffer
	}

	private function write( $what ) {
		@fwrite($this->socket, $what."\r");
	}


}