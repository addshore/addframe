<?php

namespace Addframe\Mediawiki\Api;

/**
 * Class UsageException
 */
class UsageException extends \Exception{

	private $mCodestr = '';
	private $mExtrastr = '';

	/**
	 * @param array $errorArray errorArray from the API (containing 'code' and 'message' elements)
	 * @param string $extra extra infomation (maybe a serilized object to look at)
	 */
	public function __construct( $errorArray, $extra = '' ) {
		$this->mCodestr = $errorArray['code'];
		$this->mExtrastr = $extra;
		$message = $errorArray['code'] . ': ' . $errorArray['info'];

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
		$msg = $this->getMessage();
		if( !$this->mExtrastr === '' ){
			$msg .= ': ' . $this->mExtrastr;
		}
		return $msg;
	}

}