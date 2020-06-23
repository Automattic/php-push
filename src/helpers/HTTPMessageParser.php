<?php
declare( strict_types = 1 );

class HTTPMessageParser {

	private $http_version;
	private $status_code;
	private $headers = [];
	private $body = '';

	function __construct( $text ) {
		$lines = explode( PHP_EOL, $text );
		$line = trim( array_shift( $lines ) );

		if ( ! strstr( $line, 'HTTP' ) ) {
			throw new InvalidArgumentException( 'Invalid Response: HTTP Header Missing in ' . $text );
		}

		list( $http_version, $status_code ) = explode( ' ', $line, 2 );
		$this->http_version = $http_version;
		$this->status_code = intval( $status_code );

		while ( true ) {
			$line = trim( array_shift( $lines ) );

			if ( empty( $line ) ) {
				break;
			}

			list( $key, $value ) = explode( ':', $line, 2 );
			$this->headers[ $key ] = trim( $value );
		}

		$this->body = trim( implode( PHP_EOL, $lines ) );
	}

	function getHttpVersion(): string {
		return $this->http_version;
	}

	function getStatusCode(): int {
		return $this->status_code;
	}

	function getHeader( string $key ): string {
		return $this->headers[ $key ];
	}

	function getBody(): string {
		return $this->body;
	}
}
