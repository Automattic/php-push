<?php
declare( strict_types = 1 );
// phpcs:disable WordPress.WP.AlternativeFunctions.curl_curl_init
// phpcs:disable WordPress.WP.AlternativeFunctions.curl_curl_setopt
// phpcs:disable WordPress.WP.AlternativeFunctions.curl_curl_exec
// phpcs:disable WordPress.WP.AlternativeFunctions.curl_curl_close

/**
 * @group e2e
 */
class APNSNetworkServiceIntegrationTest extends APNSTest {

	public function setUp(): void {
		// Ensure the server is running before each test
		$this->assertTrue( $this->reset_mock_server(), "The mock server doesn't appear to be running." );
	}

	public function testThatSendingAfterGoAwayFrameEmitsIsRecoverable() {

		$count = random_int( 1, 1000 );

		$service = ( new APNSNetworkService() )
			->set_certificate_bundle_path( dirname( __DIR__ ) . '/MockAPNSServer/test-cert.pem' )
			->set_port( 8443 );

		for ( $i = 0; $i < $count; $i++ ) {
			$service->enqueue_request( 'https://127.0.0.1/', [], '' );
		}

		$responses = $service->send_queued_requests();
		$this->assertCount( $count, $responses );

		$this->reset_mock_server();

		for ( $i = 0; $i < $count; $i++ ) {
			$service->enqueue_request( 'https://127.0.0.1/', [], '' );
		}

		$responses = $service->send_queued_requests();
		$this->assertTrue( $responses[0]->is_error() );
		$this->assertTrue( $responses[0]->should_retry() );
		$this->assertCount( $count, $responses );

		$service->close_connection();
	}

	private function reset_mock_server(): bool {
		$ch = curl_init();
		curl_setopt( $ch, CURLOPT_URL, 'https://127.0.0.1/reset' );
		curl_setopt( $ch, CURLOPT_PORT, 8443 );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, false );
		curl_setopt( $ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_2TLS );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );

		$result = curl_exec( $ch );
		curl_close( $ch );

		return false !== $result;
	}
}
