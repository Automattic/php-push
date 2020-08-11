<?php
declare( strict_types = 1 );

class APNSClient {

	/** @var APNSNetworkService **/
	private $network_service;

	/** @var APNSConfiguration */
	private $configuration;

	/** @var string */
	private $provider_token;

	public function __construct( APNSConfiguration $configuration, ?APNSNetworkService $network_service = null ) {
		$this->configuration = $configuration;
		$this->network_service = $network_service ?? new APNSNetworkService();

		$this->refreshToken();
	}

	public function setPortNumber( int $port ): void {
		$this->network_service->setPort( $port );
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

		return $this->network_service->sendQueuedRequests();
	}

	public function close(): void {
		$this->network_service->closeConnection();
	}

	public function setDebug( bool $debug ): self {
		$this->network_service->setDebug( $debug );
		return $this;
	}

	public function setDisableSSLVerification( bool $disable ): self {
		$this->network_service->setSslVerificationEnabled( ! $disable );
		return $this;
	}

	private function enqueueRequest( APNSRequest $request ): void {
		$headers = $request->getHeadersForConfiguration( $this->configuration );
		$headers = $this->convertRequestHeaders( $headers );
		$url = $request->getUrlForConfiguration( $this->configuration );

		$this->network_service->enqueueRequest( $url, $headers, $request->getBody() );
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
