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

	public function __construct( int $status_code, string $response_text, APNSResponseMetrics $metrics ) {
		$this->status_code = $status_code;
		$this->metrics = $metrics;

		$parser = new HTTPMessageParser( $response_text );
		$this->uuid = $parser->getHeader( 'apns-id' ) ?? 'Not Available';

		if ( $this->isError() ) {
			if ( ! empty( $response_text ) ) {
				$body = (object) json_decode( $parser->getBody(), false, 512, JSON_THROW_ON_ERROR );
				/** @var string */
				$reason = $body->reason;
				$this->error_message = $reason;
			} else {
				$this->error_message = '';
			}
		}
	}

	public function getUuid(): string {
		return $this->uuid;
	}

	public function getStatusCode(): int {
		return $this->status_code;
	}

	public function isError(): bool {
		return $this->status_code !== 200;
	}

	public function getErrorMessage(): ?string {
		return $this->error_message;
	}

	public function isUnrecoverableError(): bool {
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

	public function shouldUnsubscribeDevice(): bool {
		return $this->status_code === 410;
	}

	public function shouldRetry(): bool {
		return $this->status_code === 429 || $this->isServerError() || $this->status_code === 0;
	}

	public function isServerError(): bool {
		return $this->status_code >= 500;
	}
}
