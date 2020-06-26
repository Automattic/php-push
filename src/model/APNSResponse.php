<?php
declare( strict_types = 1 );

class APNSResponse {

	/** @var string */
	private $uuid;

	/** @var int */
	private $status_code;

	/** @var string|null */
	private $error_message;

	/** @var APNSResponseMetrics */
	private $metrics;

	function __construct( int $status_code, string $response_text, APNSResponseMetrics $metrics ) {
		$this->status_code = $status_code;
		$this->metrics = $metrics;

		$parser = new HTTPMessageParser( $response_text );
		$this->uuid = $parser->getHeader( 'apns-id' );

		if ( $this->isError() ) {
			$body = json_decode( $parser->getBody(), false, 512, JSON_THROW_ON_ERROR );
			$this->error_message = $body->reason;
		}
	}

	function getUuid(): string {
		return $this->uuid;
	}

	function getStatusCode(): int {
		return $this->status_code;
	}

	function isError(): bool {
		return $this->status_code !== 200;
	}

	function getErrorMessage(): ?string {
		return $this->error_message;
	}

	function isUnrecoverableError(): bool {
		return in_array(
			$this->status_code, [
				400,    // Bad request (invalid data)
				403,    // Authentication Token Error
				404,    // Invalid Device Token (Bad URL Path)
				405,    // Invalid HTTP Request Type
				410,    // The device token is no longer active
				413,    // Notification payload was too large
			], true
		);
	}

	function shouldUnsubscribeDevice(): bool {
		return $this->status_code === 410;
	}

	function shouldRetry(): bool {
		return $this->status_code === 429 || $this->isServerError();
	}

	function isServerError(): bool {
		return $this->status_code >= 500;
	}
}
