<?php
declare( strict_types = 1 );

class APNSRequest {

	// A string representing the request payload
	private $body;

	// A copy of the request metadata
	private $metadata;

	// The device token
	private $token;

	static function fromString( string $payload, string $token, APNSRequestMetadata $metadata ) {
		return new APNSRequest( $payload, $token, $metadata );
	}

	static function fromPayload( APNSPayload $payload, $token, APNSRequestMetadata $metadata ) {
		return new APNSRequest( json_encode( $payload ), $token, $metadata );
	}

	protected function __construct( string $payload, string $token, APNSRequestMetadata $metadata ) {
		$this->body = $payload;
		$this->token = $token;
		$this->metadata = $metadata;
	}

	function getToken(): string {
		return $this->token;
	}

	function getBody(): string {
		return $this->body;
	}

	function getUuid(): string {
		return $this->metadata->getUuid();
	}

	function getUrlForConfiguration( APNSConfiguration $configuration ): string {
		return $configuration->get_endpoint() . $this->token;
	}

	function getHeadersForConfiguration( APNSConfiguration $configuration ) {

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
				'metadata' => $this->metadata->toJSON(),
				'token' => $this->token,
			]
		);
	}

	public static function fromJSON( $data ): self {
		$object = json_decode( $data );

		$payload = $object->payload;
		$metadata = APNSRequestMetadata::fromJSON( $object->metadata );
		$token = $object->token;

		return new APNSRequest( $payload, $token, $metadata );
	}
}
