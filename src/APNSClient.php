<?php
declare( strict_types = 1 );

class APNSClient {

	/** @var MultiplexedNetworkService **/
	private $network_service;

	/** @var APNSConfiguration */
	private $configuration;

	/** @var string */
	private $provider_token;

	/** @var int */
	private $port_number = 443;

	/** @var bool */
	private $debug = false;

	/** @var bool */
	private $disable_ssl_verification = false;

	public function __construct( APNSConfiguration $configuration, MultiplexedNetworkService $network_service ) {
		$this->configuration = $configuration;
		$this->network_service = $network_service;

		$this->refreshToken();
	}

	public function setPortNumber( int $port ): void {
		$this->port_number = $port;
	}

	// Can't be overridden, otherwise the subclass might not correctly refresh the token
	public final function refreshToken(): void {
		$this->provider_token = $this->configuration->getProviderToken();
	}

	/**
	 * @return APNSResponse[]
	 *
	 * @psalm-return list<APNSResponse>
	 */
	public function sendRequests( array $requests ): array {
		foreach ( $requests as $request ) {
			assert( get_class( $request ) === APNSRequest::class );
			$this->enqueueRequest( $request );
		}

		return $this->sendQueuedRequests();
	}

	public function close(): void {
		$this->network_service->closeConnection();
	}

	public function setDebug( bool $debug ): self {
		$this->debug = $debug;
		return $this;
	}

	public function setDisableSSLVerification( bool $disable ): self {
		$this->disable_ssl_verification = $disable;
		return $this;
	}

	private function enqueueRequest( APNSRequest $request ): void {
		$headers = $request->getHeadersForConfiguration( $this->configuration );
		$headers = $this->convertRequestHeaders( $headers );
		$url = $request->getUrlForConfiguration( $this->configuration );

		$this->network_service->enqueueRequest( new Request( $url, $this->port_number, $headers, $request->getBody(), $this->debug, ! $this->disable_ssl_verification ) );
	}

	/**
	 * @return APNSResponse[]
	 *
	 * @psalm-return list<APNSResponse>
	 */
	private function sendQueuedRequests(): array {

		$network_responses = $this->network_service->sendQueuedRequests();

		$apns_responses = [];

		foreach ( $network_responses as $response ) {
			$apns_responses[] = $this->processResponse( $response );
		}

		return $apns_responses;
	}

	/**
	 * Parse a Response from the network layer into an APNSResponse object
	 *
	 * @param Response $response
	 */
	private function processResponse( $response ): APNSResponse {
		$metrics = new APNSResponseMetrics( $response->total_bytes, $response->transfer_time );
		return new APNSResponse( $response->status_code, $response->text, $metrics );
	}

	/**
	 *
	 * @param array $headers
	 */
	private function convertRequestHeaders( array $headers ): array {
		$_headers = [];
		foreach ( $headers as $key => $value ) {
			$_headers[] = $key . ': ' . $value;
		}
		return $_headers;
	}
}
