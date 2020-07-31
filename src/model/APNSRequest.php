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
		$payload = new APNSPayload( $payload );
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
	 * @return (int|mixed|null|string)[]
	 *
	 * @psalm-return array{authorization: string, content-type: string, content-length: int, apns-expiration: int, apns-push-type: string, apns-topic: mixed, user-agent?: null|string, apns-priority?: mixed, apns-id?: string, apns-collapse-id?: null|string}
	 */
	function getHeadersForConfiguration( APNSConfiguration $configuration ): array {

		$headers = [
			// Typical HTTP Headers
			'authorization' => 'bearer ' . $configuration->getProviderToken(),
			'content-type' => 'application/json',
			'content-length' => strlen( $this->body ),

			// Apple-specific Required Headers
			'apns-expiration' => $this->metadata->getExpirationTimestamp(),
			'apns-push-type' => $this->metadata->getPushType(),
			'apns-topic' => $this->metadata->getTopic(),
		];

		// A developer-provided user agent is optional, and if it's not set, the transport mechanism can manually specify this
		if ( ! is_null( $configuration->getUserAgent() ) ) {
			$headers['user-agent'] = $configuration->getUserAgent();
		}

		// Only include priority if it has been specifically set by the developer
		if ( $this->metadata->getPriority() !== 10 ) {
			$headers['apns-priority'] = $this->metadata->getPriority();
		}

		if ( ! is_null( $this->metadata->getUuid() ) ) {
			$headers['apns-id'] = $this->metadata->getUuid();
		}

		// Collapse identifier is optional – we won't set it unless it's present, because an empty value is a valid collapse identifer
		// and this would group notifications together in a way we don't want
		if ( ! is_null( $this->metadata->getCollapseIdentifier() ) ) {
			$headers['apns-collapse-id'] = $this->metadata->getCollapseIdentifier();
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
		$object = json_decode( $data );

		if ( ! property_exists( $object, 'payload' ) || is_null( $object->payload ) ) {
			throw new InvalidArgumentException( 'Unable to unserialize object – `payload` is invalid' );
		}

		if ( ! property_exists( $object, 'token' ) || is_null( $object->token ) ) {
			throw new InvalidArgumentException( 'Unable to unserialize object – `token` is invalid' );
		}

		if ( ! property_exists( $object, 'metadata' ) || is_null( $object->metadata ) ) {
			throw new InvalidArgumentException( 'Unable to unserialize object – `metadata` is invalid' );
		}

		return new APNSRequest( $object->payload, $object->token, APNSRequestMetadata::fromJSON( $object->metadata ) );
	}
}
