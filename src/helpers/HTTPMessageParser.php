<?php
declare( strict_types = 1 );

class HTTPMessageParser {

	/** @var string */
	private $http_version = '2.0';

	/** @var int */
	private $status_code = 0;

	/** @var string[] */
	private $headers = [];

	/** @var string */
	private $body = '';

	public function __construct( string $text ) {

		if ( empty( $text ) ) {
			// If we can't make heads or tails of the response, it's probably because of a crashed request – set the defaults and move on
			return;
		}

		$lines = explode( PHP_EOL, $text );
		$line  = trim( array_shift( $lines ) );

		if ( ! strstr( $line, 'HTTP' ) ) {
			throw new InvalidArgumentException( 'Invalid Response: HTTP Header Missing in ' . $text );
		}

		list( $http_version, $status_code ) = explode( ' ', $line, 2 );
		$this->http_version                 = $http_version;
		$this->status_code                  = intval( $status_code );

		while ( true ) {
			$line = trim( array_shift( $lines ) );

			if ( empty( $line ) ) {
				break;
			}

			list( $key, $value )   = explode( ':', $line, 2 );
			$this->headers[ $key ] = trim( $value );
		}

		$this->body = trim( implode( PHP_EOL, $lines ) );
	}

	public function get_http_version(): string {
		return $this->http_version;
	}

	public function get_status_code(): int {
		return $this->status_code;
	}

	public function get_header( string $key ): ?string {
		if ( ! isset( $this->headers[ $key ] ) ) {
			return null;
		}

		return $this->headers[ $key ];
	}

	public function get_body(): string {
		return $this->body;
	}
}
