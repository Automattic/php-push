<?php
declare( strict_types = 1 );

class APNSClient {

	private $curl_handle;

	private $configuration;
	private $provider_token;
	private $port_number = 443;

	private $debug = false;
	private $disable_ssl_verification = false;

	public function __construct( APNSConfiguration $configuration ) {
		$this->configuration = $configuration;

		$ch = curl_multi_init();
		curl_multi_setopt( $ch, CURLMOPT_PIPELINING, CURLPIPE_MULTIPLEX );
		curl_multi_setopt( $ch, CURLMOPT_MAX_TOTAL_CONNECTIONS, 1 );
		curl_multi_setopt( $ch, CURLMOPT_MAX_PIPELINE_LENGTH, 1000 );
		$this->curl_handle = $ch;

		$this->refreshToken();
	}

	public function setPortNumber( int $port ) {
		$this->port_number = $port;
	}

	public function refreshToken() {
		$this->provider_token = $this->configuration->getProviderToken();
	}

	public function sendRequests( array $requests ) {
		foreach ( $requests as $request ) {
			assert( get_class( $request ) === APNSRequest::class );
			$this->enqueueRequest( $request );
		}

		return $this->sendQueuedRequests();
	}

	public function close() {
		return curl_multi_close( $this->curl_handle );
	}

	public function setDebug( bool $debug ): self {
		$this->debug = $debug;
		return $this;
	}

	public function setDisableSSLVerification( bool $disable ): self {
		$this->disable_ssl_verification = $disable;
		return $this;
	}

	private function enqueueRequest( APNSRequest $request ) {
		$headers = $request->getHeadersForConfiguration( $this->configuration );
		$headers = $this->convertRequestHeaders( $headers );

		$ch = curl_init( $request->getUrlForConfiguration( $this->configuration ) );
		curl_setopt( $ch, CURLOPT_HEADER, true );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_2TLS );
		curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers );
		curl_setopt( $ch, CURLOPT_POST, true );
		curl_setopt( $ch, CURLOPT_POSTFIELDS, $request->getBody() );
		curl_setopt( $ch, CURLOPT_VERBOSE, $this->debug );
		curl_setopt( $ch, CURLOPT_PORT, $this->port_number );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, ! $this->disable_ssl_verification );

		curl_multi_add_handle( $this->curl_handle, $ch );
	}

	private function sendQueuedRequests() {

		$responses = [];

		do {
			$status = curl_multi_exec( $this->curl_handle, $running_operation_count );

			while ( true ) {

				$info = curl_multi_info_read( $this->curl_handle );

				if ( ! $info ) {
					break;
				}

				if ( $info['result'] !== CURLE_OK ) {
					throw new Exception( 'Request failed: ' . $info['result'] );
				}

				if ( $info && ! is_null( $info['handle'] ) ) {
					$handle = $info['handle'];
					$responses[] = $this->processResponse( $handle );

					curl_multi_remove_handle( $this->curl_handle, $handle );
					curl_close( $handle );
				}
			}
		} while ( $running_operation_count > 0 && $status === CURLM_OK );

		if ( $status !== CURLM_OK ) {
			throw new Exception( 'Unable to continue sending – ' . curl_multi_strerror( $status ) );
		}

		return $responses;
	}

	private function processResponse( $handle ): APNSResponse {
		// Error Code and Details
		$status_code = intval( curl_getinfo( $handle, CURLINFO_HTTP_CODE ) );
		$response_text = curl_multi_getcontent( $handle );

		// Interesting Request Metrics for stats
		$transfer_time = curl_getinfo( $handle, CURLINFO_TOTAL_TIME_T );
		$total_bytes = curl_getinfo( $handle, CURLINFO_SIZE_UPLOAD_T );

		$metrics = new APNSResponseMetrics( $total_bytes, $transfer_time );
		return new APNSResponse( $status_code, $response_text, $metrics );
	}

	private function convertRequestHeaders( $_headers ) {
		$headers = [];
		foreach ( $_headers as $key => $value ) {
			$headers[] = $key . ': ' . $value;
		}
		return $headers;
	}
}
