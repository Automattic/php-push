<?php
declare( strict_types = 1 );

class CurlMultiplexedNetworkService implements MultiplexedNetworkService {

	/** @var resource **/
	private $curl_handle;

	public function __construct() {
		$ch = curl_multi_init();
		curl_multi_setopt( $ch, CURLMOPT_PIPELINING, CURLPIPE_MULTIPLEX );
		curl_multi_setopt( $ch, CURLMOPT_MAX_TOTAL_CONNECTIONS, 1 );
		curl_multi_setopt( $ch, CURLMOPT_MAX_PIPELINE_LENGTH, 1000 );

		$this->curl_handle = $ch;
	}

	public function enqueueRequest( string $url, int $port, array $headers, string $body, bool $debug, bool $ssl_verification_enabled ): void {
		$ch = curl_init( $url );
		curl_setopt( $ch, CURLOPT_HEADER, true );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_2TLS );
		curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers );
		curl_setopt( $ch, CURLOPT_POST, true );
		curl_setopt( $ch, CURLOPT_POSTFIELDS, $body );
		curl_setopt( $ch, CURLOPT_VERBOSE, $debug );
		curl_setopt( $ch, CURLOPT_PORT, $port );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, $ssl_verification_enabled );

		curl_multi_add_handle( $this->curl_handle, $ch );
	}

	public function execute( int &$still_running ): int {
		return curl_multi_exec( $this->curl_handle, $still_running );
	}

	/**
	 * @return Response[]
	 *
	 * @psalm-return list<Response>
	 */
	public function sendQueuedRequests(): array {

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

				if ( ! is_null( $info['handle'] ) ) {
					$handle = $info['handle'];
					$responses[] = $this->process( $handle );

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

	/**
	 * @param resource $handle
	 */
	private function process( $handle ): Response {
		// Error Code and Details
		$status_code = intval( curl_getinfo( $handle, CURLINFO_HTTP_CODE ) );
		$response_text = curl_multi_getcontent( $handle );

		// Interesting Request Metrics for stats
		$transfer_time = curl_getinfo( $handle, CURLINFO_TOTAL_TIME_T );
		$total_bytes = curl_getinfo( $handle, CURLINFO_SIZE_UPLOAD_T );

		return new Response( $status_code, $response_text, $transfer_time, $total_bytes );
	}

	public function closeConnection(): void {
		curl_multi_close( $this->curl_handle );
	}
}

