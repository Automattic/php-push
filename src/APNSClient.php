<?php
declare( strict_types = 1 );

class APNSClient {

	/** @var APNSNetworkService **/
	private $network_service;

	/** @var APNSConfiguration */
	private $configuration;

	public function __construct( APNSConfiguration $configuration, ?APNSNetworkService $network_service = null ) {
		$this->configuration   = $configuration;
		$this->network_service = $network_service ?? new APNSNetworkService();
	}

	public static function with_configuration( APNSConfiguration $configuration, ?APNSNetworkService $network_service = null ): self {
		return new APNSClient( $configuration, $network_service );
	}

	public function set_port_number( int $port ): self {
		$this->network_service->set_port( $port );
		return $this;
	}

	/**
	 * @param APNSRequest[] $requests
	 *
	 * @return APNSResponse[]
	 */
	public function send_requests( array $requests ): array {
		foreach ( $requests as $request ) {
			assert( get_class( $request ) === APNSRequest::class );

			$url     = $request->get_url_for_configuration( $this->configuration );
			$headers = $request->get_headers_for_configuration( $this->configuration );
			$this->network_service->enqueue_request( $url, $this->convert_request_headers( $headers ), $request->get_body() );
		}

		return $this->network_service->send_queued_requests();
	}

	public function close(): self {
		$this->network_service->close_connection();
		return $this;
	}

	public function set_debug( bool $debug ): self {
		$this->network_service->set_debug( $debug );
		return $this;
	}

	public function set_certificate_bundle_path( string $path ): self {
		$this->network_service->set_certificate_bundle_path( $path );
		return $this;
	}

	/**
	 *
	 * @param array $headers
	 * @psalm-param array<string, string> $headers
	 *
	 * @return string[]
	 */
	private function convert_request_headers( array $headers ): array {
		$_headers = [];
		foreach ( $headers as $key => $value ) {
			$_headers[] = $key . ': ' . $value;
		}
		return $_headers;
	}
}
