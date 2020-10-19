<?php
declare( strict_types = 1 );
// phpcs:disable WordPress.WP.AlternativeFunctions.json_encode_json_encode

class APNSRequest {

	/**
	 * A string representing the request payload
	 *
	 * @var string
	 */
	private $body;

	/**
	 * A copy of the request metadata
	 *
	 * @var APNSRequestMetadata
	 */
	private $metadata;

	/**
	 * A copy of the device token
	 *
	 * @var string
	 */
	private $token;

	/**
	 * Data that should be passed back in `APNSResponse`. By default, it will always contain the following fields:
	 * - `apns_token`: The provided token – this can be used to delete invalid tokens from your database
	 * - `apns_uuid`: The request UUID – this can be used to increment a `retry` field or to delete a given request from a backing store
	 *
	 * @var object
	 */
	private $userdata;

	public static function from_string( string $payload, string $token, APNSRequestMetadata $metadata, ?object $userdata = null ): self {
		$payload = APNSPayload::from_string( $payload );
		return new APNSRequest( $payload->to_json(), $token, $metadata, $userdata );
	}

	public static function from_payload( APNSPayload $payload, string $token, APNSRequestMetadata $metadata, ?object $userdata = null ): self {
		return new APNSRequest( $payload->to_json(), $token, $metadata, $userdata );
	}

	protected function __construct( string $payload, string $token, APNSRequestMetadata $metadata, ?object $userdata = null ) {
		$this->body     = $payload;
		$this->token    = $token;
		$this->metadata = $metadata;
		$this->userdata = (object) array_merge(
			(array) $userdata,
			[
				'apns_token' => $token,
				'apns_uuid'  => $metadata->get_uuid(),
			]
		);
	}

	public function get_token(): string {
		return $this->token;
	}

	public function get_metadata(): APNSRequestMetadata {
		return $this->metadata;
	}

	public function get_body(): string {
		return $this->body;
	}

	public function get_uuid(): string {
		return $this->metadata->get_uuid();
	}

	public function get_url_for_configuration( APNSConfiguration $configuration ): string {
		return $configuration->get_endpoint() . $this->token;
	}

	public function get_userdata(): object {
		return $this->userdata;
	}

	/**
	 * @return string[]
	 *
	 * @psalm-return array<string, string>
	 */
	public function get_headers_for_configuration( APNSConfiguration $configuration ): array {

		$headers = [
			// Typical HTTP Headers
			'authorization'   => 'bearer ' . $configuration->get_provider_token(),
			'content-type'    => 'application/json',
			'content-length'  => strval( strlen( $this->body ) ),

			// Apple-specific Required Headers
			'apns-expiration' => strval( $this->metadata->get_expiration_timestamp() ),
			'apns-push-type'  => $this->metadata->get_push_type(),
			'apns-topic'      => $this->metadata->get_topic(),
		];

		// A developer-provided user agent is optional, and if it's not set, the transport mechanism can manually specify this
		if ( ! is_null( $configuration->get_user_agent() ) ) {
			$headers['user-agent'] = $configuration->get_user_agent() ?? 'unknown';
		}

		// Only include priority if it has been specifically set by the developer
		if ( $this->metadata->get_priority() !== 10 ) {
			$headers['apns-priority'] = strval( $this->metadata->get_priority() );
		}

		if ( ! is_null( $this->metadata->get_uuid() ) ) {
			$headers['apns-id'] = $this->metadata->get_uuid();
		}

		// Collapse identifier is optional – we won't set it unless it's present, because an empty value is a valid collapse identifer
		// and this would group notifications together in a way we don't want
		$collapse_identifier = $this->metadata->get_collapse_identifier();
		if ( ! is_null( $collapse_identifier ) ) {
			$headers['apns-collapse-id'] = $collapse_identifier;
		}

		return $headers;
	}

	public function to_json(): string {
		return json_encode(
			[
				'payload'  => $this->body,
				'token'    => $this->token,
				'metadata' => $this->metadata->to_json(),
			]
		);
	}

	public static function from_json( string $data ): self {
		$object = (object) json_decode( $data, false, 512, JSON_THROW_ON_ERROR );

		if ( ! property_exists( $object, 'payload' ) || is_null( $object->payload ) || ! is_string( $object->payload ) ) {
			throw new InvalidArgumentException( 'Unable to unserialize object – `payload` is invalid' );
		}

		if ( ! property_exists( $object, 'token' ) || is_null( $object->token ) || ! is_string( $object->token ) ) {
			throw new InvalidArgumentException( 'Unable to unserialize object – `token` is invalid' );
		}

		if ( ! property_exists( $object, 'metadata' ) || is_null( $object->metadata ) || ! is_string( $object->metadata ) ) {
			throw new InvalidArgumentException( 'Unable to unserialize object – `metadata` is invalid' );
		}

		return new APNSRequest( $object->payload, $object->token, APNSRequestMetadata::from_json( $object->metadata ) );
	}
}
