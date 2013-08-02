<?php

namespace Addframe;

/**
 * Class IrcChannel
 *
 * @since 0.0.2
 * @author Addshore
 */

class Channel  {

	protected $server;
	protected $name;

	public function __construct( Server $server, $channelName ) {
		$this->server = $server;
		$this->name = $channelName;
	}

	public function getName(){
		return $this->name;
	}

	public function join( ){
		$this->server->write("JOIN ".$this->name);
	}

	public function part( ){
		$this->server->write("JOIN ".$this->name);
	}

	public function cycle( ){
		$this->part();
		$this->join();
	}

	//todo send a message
}