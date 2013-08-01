<?php

namespace Addframe;

/**
 * Class IrcChannel
 *
 * @since 0.0.2
 * @author Addshore
 */

class IrcChannel  {

	protected $server;
	protected $name;

	public function __construct( IrcServer $server, $channelName ) {
		$this->server = $server;
		$this->name = $channelName;
	}

	public function getName(){
		return $this->name;
	}

	//todo send a message
	//todo part
	//todo join
	//todo cycle
}