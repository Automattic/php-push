<?php
declare( strict_types = 1 );

class Request {

	/** @var string */
	public $url;

	/** @var int */
	public $port;

	/** @var array */
	public $headers;

	/** @var string */
	public $body;

	/** @var bool */
	public $debug;

	/** @var bool */
	public $ssl_verification_enabled;

	public function __construct( string $url, int $port, array $headers, string $body, bool $debug, bool $ssl_verification_enabled ) {
		$this->url = $url;
		$this->port = $port;
		$this->headers = $headers;
		$this->body = $body;
		$this->debug = $debug;
		$this->ssl_verification_enabled = $ssl_verification_enabled;
	}
}
