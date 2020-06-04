<?php
declare( strict_types = 1 );

class APNSClient {

	private $curl_handle;

	private $configuration;
	private $provider_token;
	private $port_number = 443;

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

	public function sendPush( APNSPush $push, $token ) {
		$request = new APNSRequest( $push, $this->configuration );

		curl_setopt( $this->curl_handle, CURLOPT_URL, $request->getUrlForToken( $token ) );
		curl_setopt( $this->curl_handle, CURLOPT_POSTFIELDS, $request->getBody() );
		curl_setopt( $this->curl_handle, CURLOPT_HTTPHEADER, $this->convertRequestHeaders( $request->getHeaders() ) );

		$result = curl_exec( $this->curl_handle );

		if ( false === $result ) {
			return curl_error( $this->curl_handle );
		}

		return $result;
	}

	public function sendPushes( array $pushes ) {

		foreach ( $pushes as $envelope ) {
			list( $push, $token ) = $envelope;
			$handle = $this->requestFromPush( $push, $token );
			curl_multi_add_handle( $this->curl_handle, $handle );
		}

		global $start;
		$time = microtime( true ) - $start;
		echo PHP_EOL . '=== Encoded in ' . $time . ' seconds === ' . PHP_EOL;

		$this->flush();
	}

	public function sendPayloads( array $payloads ) {

		foreach ( $payloads as $row ) {
			$this->enqueuePayload( $row->payload, $row->token );
		}

		global $start;
		$time = microtime( true ) - $start;
		echo PHP_EOL . '=== Encoded in ' . $time . ' seconds === ' . PHP_EOL;

		$this->flush();
	}

	public function close() {
		return curl_multi_close( $this->curl_handle );
	}

	private function requestFromPush( APNSPush $push, $token ) {
		$request = new APNSRequest( $push, $this->configuration );
		return $this->handleFromRequest( $request, $token );
	}

	private function enqueuePayload( string $payload, $token ) {
		$request = new APNSRequest( $payload, $this->configuration );
		$handle = $this->handleFromRequest( $request, $token );
		curl_multi_add_handle( $this->curl_handle, $handle );
	}

	private function handleFromRequest( APNSRequest $request, $token ) {
		$headers = $this->convertRequestHeaders( $request->getHeaders() );

		$ch = curl_init( $request->getUrlForToken( $token ) );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		// We know the server supports HTTP2, so we can avoid the HTTP1.1 => 2 upgrade
		curl_setopt( $ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_2_PRIOR_KNOWLEDGE );
		curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers );
		curl_setopt( $ch, CURLOPT_POST, 1 );
		curl_setopt( $ch, CURLOPT_POSTFIELDS, $request->getBody() );
		curl_setopt( $ch, CURLOPT_VERBOSE, false );
		curl_setopt( $ch, CURLOPT_PORT, $this->port_number );

		return $ch;
	}

	private function flush() {

		do {
			$status = curl_multi_exec( $this->curl_handle, $active );

			while ( true ) {

				$info = curl_multi_info_read( $this->curl_handle );

				if ( ! $info ) {
					break;
				}

				if ( ! $info ) {
					throw new Exception( "Couldn't send request" );
				}

				if ( $info['result'] !== CURLE_OK ) {
					throw new Exception( 'Request failed: ' . $info['result'] );
				}

				if ( $info && ! is_null( $info['handle'] ) ) {
					$handle = $info['handle'];
					// $url = curl_getinfo( $handle, CURLINFO_EFFECTIVE_URL );
					// $status_code = curl_getinfo( $handle, CURLINFO_HTTP_CODE );
					// $bytes_uploaded = curl_getinfo( $handle, CURLINFO_SIZE_UPLOAD );
					// $body = curl_multi_getcontent( $handle );
					echo '.';
					// echo "\n\nResponse:\n";
					// echo "URL:\t\t$url\n";
					// echo "Status Code:\t$status_code \n";
					// echo "Response Body:\t$body\n";
					// echo "\n===\n";
					// echo "\nStats:\n";
					// echo "Bytes Sent:\t$bytes_uploaded\n";
					curl_multi_remove_handle( $this->curl_handle, $handle );
					curl_close( $handle );
				}
			}
		} while ( $active && $status === CURLM_OK );

		if ( $status !== CURLM_OK ) {
			throw new Exception( 'Unable to continue sending – ' . curl_multi_strerror( $status ) );
		}
	}

	private function convertRequestHeaders( $_headers ) {
		$headers = [];
		foreach ( $_headers as $key => $value ) {
			$headers[] = $key . ': ' . $value;
		}
		return $headers;
	}
}
