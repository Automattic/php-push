<?php
declare( strict_types = 1 );

class Response {

	/** @var int */
	public $status_code;

	/** @var string */
	public $text;

	/** @var int */
	public $transfer_time;

	/** @var int */
	public $total_bytes;

	public function __construct( int $status_code, string $text, int $transfer_time, int $total_bytes ) {
		$this->status_code = $status_code;
		$this->text = $text;
		$this->transfer_time = $transfer_time;
		$this->total_bytes = $total_bytes;
	}
}
