<?php
declare( strict_types = 1 );

class APNSNetworkService {

	/** @var resource **/
	private $curl_handle;

	/** @var int */
	public $port = 443;

	/** @var bool **/
	private $debug = false;

	/**
	 * An optional path to the certificate bundle libcurl should use. By default, we'll use the system one, but on some systems it's necessary
	 * to override this. One example is Debian, where Apple's Geotrust certificate isn't trusted. (https://bugs.debian.org/cgi-bin/bugreport.cgi?bug=962596)
	 *
	 * @var ?string
	 **/
	private $certificate_bundle_path = null;

	public function __construct() {
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
	}

	public function setPort( int $port ): self {
		$this->port = $port;
		return $this;
	}

	public function setDebug( bool $debug ): self {
		$this->debug = $debug;
		return $this;
	}

	public function setCertificateBundlePath( string $path ): self {
		if ( ! file_exists( $path ) ) {
			throw new InvalidArgumentException( 'There is no certificate bundle at ' . $path );
		}
		$this->certificate_bundle_path = $path;
		return $this;
	}

	public function enqueueRequest( string $url, array $headers, string $body ): void {
		$ch = curl_init( $url );
		curl_setopt( $ch, CURLOPT_HEADER, true );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_2TLS );
		curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers );
		curl_setopt( $ch, CURLOPT_POST, true );
		curl_setopt( $ch, CURLOPT_POSTFIELDS, $body );
		curl_setopt( $ch, CURLOPT_VERBOSE, $this->debug );
		curl_setopt( $ch, CURLOPT_PORT, $this->port );

		if ( ! is_null( $this->certificate_bundle_path ) ) {
			curl_setopt( $ch, CURLOPT_CAINFO, $this->certificate_bundle_path );
		}

		curl_multi_add_handle( $this->curl_handle, $ch );
	}

	/**
	 * @return APNSResponse[]
	 *
	 * @psalm-return list<APNSResponse>
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

				$result = intval( $info['result'] );

				if ( $result !== CURLE_OK ) {
					error_log( 'Request failed: ' . $result );
				}

				if ( ! is_null( $info['handle'] ) ) {
					/** @var resource */
					$handle = $info['handle'];
					$responses[] = $this->process( $handle );

					curl_multi_remove_handle( $this->curl_handle, $handle );
					curl_close( $handle );
				}
			}
		} while ( $running_operation_count > 0 && $status === CURLM_OK );

		if ( $status !== CURLM_OK ) {
			throw new Exception( 'Unable to continue sending – ' . ( curl_multi_strerror( $status ) ?? 'unknown error' ) );
		}

		return $responses;
	}

	/**
	 * @param resource $handle
	 */
	private function process( $handle ): APNSResponse {
		// Error Code and Details
		$status_code = intval( curl_getinfo( $handle, CURLINFO_HTTP_CODE ) );
		$response_text = curl_multi_getcontent( $handle );

		// Interesting Request Metrics for stats
		$transfer_time = intval( curl_getinfo( $handle, CURLINFO_TOTAL_TIME_T ) ); // as microseconds
		$total_bytes = intval( curl_getinfo( $handle, CURLINFO_SIZE_UPLOAD_T ) );

		$metrics = new APNSResponseMetrics( $total_bytes, $transfer_time );

		return new APNSResponse( $status_code, $response_text, $metrics );
	}

	public function closeConnection(): void {
		curl_multi_close( $this->curl_handle );
	}
}
