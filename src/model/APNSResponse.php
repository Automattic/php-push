<?php
declare( strict_types = 1 );

class APNSResponse {

	/**
	 * The UUID associated with the APNS request
	 *
	 * @var string
	 */
	private $uuid;

	/**
	 * The Device Token the APNS request was sent to
	 *
	 * @var string
	 */
	private $token;

	/**
	 * The HTTP status code of the response
	 *
	 * @var int
	 */
	private $status_code;

	/**
	 * The error message returned by the APNS service, if any
	 *
	 * @var string|null
	 */
	private $error_message;

	/**
	 * Response metrics for performance monitoring
	 *
	 * @var APNSResponseMetrics
	 */
	private $metrics;

	/**
	 * The $userdata object that was passed to the original APNSRequest
	 *
	 * @var object
	 */
	private $userdata;

	public function __construct( int $status_code, string $response_text, APNSResponseMetrics $metrics, object $userdata ) {
		$this->status_code = $status_code;
		$this->metrics     = $metrics;
		$this->uuid        = strval( $userdata->apns_uuid );
		$this->token       = strval( $userdata->apns_token );
		$this->userdata    = $userdata;

		if ( $this->is_error() ) {
			if ( ! empty( $response_text ) ) {
				$parser = new HTTPMessageParser( $response_text );
				$body   = (object) json_decode( $parser->get_body(), false, 512, JSON_THROW_ON_ERROR );

				if ( isset( $body->reason ) && is_string( $body->reason ) ) {
					$this->error_message = $body->reason;
				}
			} else {
				$this->error_message = '';
			}
		}
	}

	public function get_uuid(): string {
		return $this->uuid;
	}

	public function get_token(): string {
		return $this->token;
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
