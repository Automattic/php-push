<?php
declare( strict_types = 1 );

class APNSResponseMetrics {

	private $request_size;
	private $request_time;

	public function __construct( int $request_size = 0, int $request_time = 0 ) {
		$this->request_size = $request_size;
		$this->request_time = $request_time;
	}
}
