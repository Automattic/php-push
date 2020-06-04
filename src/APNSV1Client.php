<?php
declare( strict_types = 1 );

class APNSV1Client implements APNSClient {

	private $stream;

	function __construct() {
		$this->stream = stream_context_create();

	}
}
