<?php
declare( strict_types = 1 );

class APNSNetworkService {

	/** @var resource **/
	private $curl_handle;

	/** @var bool **/
	private $debug;

	/** @var bool **/
	private $ssl_verification_enabled;

	public function __construct( bool $debug = false, bool $ssl_verification_enabled = true ) {
		$ch = curl_multi_init();
		curl_multi_setopt( $ch, CURLMOPT_PIPELINING, CURLPIPE_MULTIPLEX );
		curl_multi_setopt( $ch, CURLMOPT_MAX_TOTAL_CONNECTIONS, 1 );
		// TODO: Setting CURLOPT_PIPEWAIT results in the following error
		//
		// ```
		// PHP Warning:  curl_multi_setopt(): Invalid curl multi configuration option in php-push/src/networking/APNSNetworkService.php on line 19
		// PHP Stack trace:
		// PHP   1. {main}() php-push/index.php:0
		// PHP   2. APNSClient->__construct() php-push/index.php:19
		// PHP   3. APNSNetworkService->__construct() php-push/src/APNSClient.php:20
		// PHP   4. curl_multi_setopt() php-push/src/networking/APNSNetworkService.php:19
		//
		// Warning: curl_multi_setopt(): Invalid curl multi configuration option in php-push/src/networking/APNSNetworkService.php on line 19
		//
		// Call Stack:
		// 0.0005     408072   1. {main}() php-push/index.php:0
		// 0.0229    2340472   2. APNSClient->__construct() php-push/index.php:19
		// 0.0231    2354752   3. APNSNetworkService->__construct() php-push/src/APNSClient.php:20
		// 0.0238    2354880   4. curl_multi_setopt() php-push/src/networking/APNSNetworkService.php:19
		// ```
		//
		// The docs can be found here: // https://curl.haxx.se/libcurl/c/CURLOPT_PIPEWAIT.html
		//
		// curl_multi_setopt( $ch, CURLOPT_PIPEWAIT, 1 );

		$this->curl_handle = $ch;
		$this->debug = $debug;
		$this->ssl_verification_enabled = $ssl_verification_enabled;
	}

	public function setDebug( bool $debug ): self {
		$this->debug = $debug;
		return $this;
	}

	public function setSslVerificationEnabled( bool $ssl_verification_enabled ): self {
		$this->ssl_verification_enabled = $ssl_verification_enabled;
		return $this;
	}

	public function enqueueRequest( Request $request ): void {
		$ch = curl_init( $request->url );
		curl_setopt( $ch, CURLOPT_HEADER, true );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_2TLS );
		curl_setopt( $ch, CURLOPT_HTTPHEADER, $request->headers );
		curl_setopt( $ch, CURLOPT_POST, true );
		curl_setopt( $ch, CURLOPT_POSTFIELDS, $request->body );
		curl_setopt( $ch, CURLOPT_VERBOSE, $this->debug );
		curl_setopt( $ch, CURLOPT_PORT, $request->port );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, $this->ssl_verification_enabled );

		curl_multi_add_handle( $this->curl_handle, $ch );
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

