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
		$this->metrics     = $metrics;

		$parser     = new HTTPMessageParser( $response_text );
		$this->uuid = $parser->get_header( 'apns-id' ) ?? 'Not Available';

		if ( $this->is_error() ) {
			if ( ! empty( $response_text ) ) {
				$body = (object) json_decode( $parser->get_body(), false, 512, JSON_THROW_ON_ERROR );
				/** @var string */
				$reason              = $body->reason;
				$this->error_message = $reason;
			} else {
				$this->error_message = '';
			}
		}
	}

	public function get_uuid(): string {
		return $this->uuid;
	}

	public function get_status_code(): int {
		return $this->status_code;
	}

	public function is_error(): bool {
		return 200 !== $this->status_code;
	}

	public function get_error_message(): ?string {
		return $this->error_message;
	}

	public function is_unrecoverable_error(): bool {
		return in_array(
			$this->status_code,
			[
				400,    // Bad request (invalid data)
				403,    // Authentication Token Error
				404,    // Invalid Device Token (Bad URL Path)
				405,    // Invalid HTTP Request Type
				410,    // The device token is no longer active
				413,    // Notification payload was too large
			],
			true
		);
	}

	public function should_unsubscribe_device(): bool {
		return 410 === $this->status_code;
	}

	public function should_retry(): bool {
		return 429 === $this->status_code || $this->is_server_error() || 0 === $this->status_code;
	}

	public function is_server_error(): bool {
		return 500 <= $this->status_code;
	}
}
