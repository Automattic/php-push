<?php
declare( strict_types = 1 );

class APNSRequest {

	// A string representing the request payload
	private $body;

	// A copy of the request metadata
	private $metadata;

	// The device token
	private $token;

	static function fromString( string $payload, string $token, APNSRequestMetadata $metadata ): self {
		$payload = APNSPayload::fromString( $payload );
		return new APNSRequest( $payload->toJSON(), $token, $metadata );
	}

	static function fromPayload( APNSPayload $payload, string $token, APNSRequestMetadata $metadata ): self {
		return new APNSRequest( $payload->toJSON(), $token, $metadata );
	}

	protected function __construct( string $payload, string $token, APNSRequestMetadata $metadata ) {
		$this->body = $payload;
		$this->token = $token;
		$this->metadata = $metadata;
	}

	function getToken(): string {
		return $this->token;
	}

	function getMetadata(): APNSRequestMetadata {
		return $this->metadata;
	}

	function getBody(): string {
		return $this->body;
	}

	function getUuid(): string {
		return $this->metadata->getUuid();
	}

	function getUrlForConfiguration( APNSConfiguration $configuration ): string {
		return $configuration->getEndpoint() . $this->token;
	}

	/**
	 * @return string[]
	 *
	 * @psalm-return array<string, string>
	 */
	function getHeadersForConfiguration( APNSConfiguration $configuration ): array {

		$headers = [
			// Typical HTTP Headers
			'authorization' => 'bearer ' . $configuration->getProviderToken(),
			'content-type' => 'application/json',
			'content-length' => strval( strlen( $this->body ) ),

			// Apple-specific Required Headers
			'apns-expiration' => strval( $this->metadata->getExpirationTimestamp() ),
			'apns-push-type' => $this->metadata->getPushType(),
			'apns-topic' => $this->metadata->getTopic(),
		];

		// A developer-provided user agent is optional, and if it's not set, the transport mechanism can manually specify this
		if ( ! is_null( $configuration->getUserAgent() ) ) {
			$headers['user-agent'] = $configuration->getUserAgent() ?? 'unknown';
		}

		// Only include priority if it has been specifically set by the developer
		if ( $this->metadata->getPriority() !== 10 ) {
			$headers['apns-priority'] = strval( $this->metadata->getPriority() );
		}

		if ( ! is_null( $this->metadata->getUuid() ) ) {
			$headers['apns-id'] = $this->metadata->getUuid();
		}

		// Collapse identifier is optional – we won't set it unless it's present, because an empty value is a valid collapse identifer
		// and this would group notifications together in a way we don't want
		$collapse_identifier = $this->metadata->getCollapseIdentifier();
		if ( ! is_null( $collapse_identifier ) ) {
			$headers['apns-collapse-id'] = $collapse_identifier;
		}

		return $headers;
	}

	public function toJSON(): string {
		return json_encode(
			[
				'payload' => $this->body,
				'token' => $this->token,
				'metadata' => $this->metadata->toJSON(),
			]
		);
	}

	public static function fromJSON( string $data ): self {
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

		return new APNSRequest( $object->payload, $object->token, APNSRequestMetadata::fromJSON( $object->metadata ) );
	}
}
