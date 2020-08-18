<?php
declare( strict_types = 1 );

class APNSClient {

	/** @var APNSNetworkService **/
	private $network_service;

	/** @var APNSConfiguration */
	private $configuration;

	public function __construct( APNSConfiguration $configuration, ?APNSNetworkService $network_service = null ) {
		$this->configuration = $configuration;
		$this->network_service = $network_service ?? new APNSNetworkService();
	}

	public static function withConfiguration( APNSConfiguration $configuration, ?APNSNetworkService $network_service = null ): self {
		return new APNSClient( $configuration, $network_service );
	}

	public function setPortNumber( int $port ): self {
		$this->network_service->setPort( $port );
		return $this;
	}

	/**
	 * @return APNSResponse[]
	 *
	 * @psalm-return list<APNSResponse>
	 */
	public function sendRequests( array $requests ): array {
		foreach ( $requests as $request ) {
			assert( get_class( $request ) === APNSRequest::class );

			$url = $request->getUrlForConfiguration( $this->configuration );
			$headers = $request->getHeadersForConfiguration( $this->configuration );
			$this->network_service->enqueueRequest( $url, $this->convertRequestHeaders( $headers ), $request->getBody() );
		}

		return $this->network_service->sendQueuedRequests();
	}

	public function close(): self {
		$this->network_service->closeConnection();
		return $this;
	}

	public function setDebug( bool $debug ): self {
		$this->network_service->setDebug( $debug );
		return $this;
	}

	public function setDisableSSLVerification( bool $disable ): self {
		$this->network_service->setSslVerificationEnabled( ! $disable );
		return $this;
	}

	public function setCertificateBundlePath( string $path ): self {
		$this->network_service->setCertificateBundlePath( $path );
		return $this;
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
