<?php
declare( strict_types = 1 );

class APNSRequest {

	// A string representing the request payload
	private $body;

	// A copy of the request metadata
	private $metadata;

	// A copy of the configuration
	private $configuration;

	function __construct( $push, APNSRequestMetadata $metadata, APNSConfiguration $configuration ) {
		$this->configuration = $configuration;

		if ( is_string( $push ) ) {
			$this->body = $push;
			return;
		}

		if ( is_object( $push ) && get_class( $push ) === APNSPayload::class ) {
			$this->body = json_encode( $push );
		}

		$this->metadata = $metadata;
	}

	function getBody() {
		return $this->body;
	}

	function getUrlForToken( string $token ): string {
		return $this->configuration->get_endpoint() . $token;
	}

	function getHeaders() {

		$headers = [
			// Typical HTTP Headers
			'authorization' => 'bearer ' . $this->configuration->getProviderToken(),
			'content-type' => 'application/json',
			'content-length' => strlen( $this->body ),

			// Apple-specific Required Headers
			'apns-expiration' => $this->metadata->getExpirationTimestamp(),
			'apns-push-type' => $this->metadata->getPushType(),
			'apns-topic' => $this->metadata->getTopic(),
		];

		// A developer-provided user agent is optional, and if it's not set, the transport mechanism can manually specify this
		if ( ! is_null( $this->configuration->getUserAgent() ) ) {
			$headers['user-agent'] = $this->configuration->getUserAgent();
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
}
