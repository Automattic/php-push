<?php
declare( strict_types = 1 );
// phpcs:disable WordPress.WP.AlternativeFunctions.json_encode_json_encode

class APNSRequest {

	// A string representing the request payload
	private $body;

	// A copy of the request metadata
	private $metadata;

	// The device token
	private $token;

	public static function from_string( string $payload, string $token, APNSRequestMetadata $metadata ): self {
		$payload = APNSPayload::from_string( $payload );
		return new APNSRequest( $payload->to_json(), $token, $metadata );
	}

	public static function from_payload( APNSPayload $payload, string $token, APNSRequestMetadata $metadata ): self {
		return new APNSRequest( $payload->to_json(), $token, $metadata );
	}

	protected function __construct( string $payload, string $token, APNSRequestMetadata $metadata ) {
		$this->body     = $payload;
		$this->token    = $token;
		$this->metadata = $metadata;
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
