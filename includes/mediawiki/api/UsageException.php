<?php

namespace Addframe\Mediawiki\Api;

/**
 * Class UsageException
 */
class UsageException extends \Exception{

	private $mCodestr = '';

	/**
	 * @param array $errorArray errorArray from the API (containing 'code' and 'message' elements)
	 */
	public function __construct( $errorArray ) {
		$this->mCodestr = $errorArray['code'];
		$message = $errorArray['info'];

		parent::__construct( $message, 0 );
	}

	/**
	 * @return string error code
	 */
	public function getCodeString() {
		return $this->mCodestr;
	}

	/**
	 * @return array
	 */
	public function getMessageArray() {
		return array(
			'code' => $this->mCodestr,
			'info' => $this->getMessage()
		);
	}

	/**
	 * @return string
	 */
	public function __toString() {
		return "{$this->getCodeString()}: {$this->getMessage()}";
	}

}