<?php

class mediawikiapiresult {

	public $statusCode;
	public $value;
	private $wrapper;

	function __construct( $value ) {
		$this->value = $value;
		$this->statusCode = null;
		$this->getDetails();
	}

	private function getDetails(){
		foreach ($this->value as $key => $returned){
			$this->wrapper = $key;
			if( isset($returned['result']) ){
				$this->statusCode = $returned['result'];
				return true;
			}else if( isset($returned['code']) ){
				$this->statusCode = $returned['code'];
				return true;
			}
		}
		return false;
	}

	public function getInside(){
		return $this->value[$this->wrapper];
	}
}