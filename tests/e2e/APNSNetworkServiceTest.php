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
			$service->enqueue_request( 'https://127.0.0.1/', [], '', $this->new_userdata() );
		}

		$responses = $service->send_queued_requests();
		$this->assertCount( $count, $responses );

		$this->reset_mock_server();

		for ( $i = 0; $i < $count; $i++ ) {
			$service->enqueue_request( 'https://127.0.0.1/', [], '', $this->new_userdata() );
		}

		$responses = $service->send_queued_requests();
		$this->assertTrue( $responses[0]->is_error() );
		$this->assertTrue( $responses[0]->should_retry() );
		$this->assertCount( $count, $responses );

		$service->close_connection();
	}

	public function testThatServerTimeoutIsRecoverable() {

		$service = ( new APNSNetworkService() )
			->set_certificate_bundle_path( dirname( __DIR__ ) . '/MockAPNSServer/test-cert.pem' )
			->set_port( 8443 )
			->set_timeout( 1 );

		$service->enqueue_request( 'https://127.0.0.1/', [], '', $this->new_userdata() );
		// It's easier to test a remote server here rather than make node work how we want
		$service->enqueue_request( 'https://httpbin.org/delay/10', [], '', $this->new_userdata() );
		$service->enqueue_request( 'https://127.0.0.1/', [], '', $this->new_userdata() );

		$responses = $service->send_queued_requests();

		$success = [];
		$failure = [];

		foreach ( $responses as $response ) {
			if ( $response->is_error() ) {
				$this->assertTrue( $response->should_retry() );
				$failure[] = $response;
			} else {
				$success[] = $response;
			}
		}

		$this->assertCount( 3, $responses );
		$this->assertCount( 2, $success );
		$this->assertCount( 1, $failure );

		$service->close_connection();
	}

	public function testThatUserDataIsPassedThroughNetworkService() {

		$userdata = $this->new_userdata();

		$service = ( new APNSNetworkService() )->set_port( 8000 );
		$service->enqueue_request( 'https://httpbin.org/status/200', [], '', $userdata );
		$responses = $service->send_queued_requests();

		$this->assertCount( 1, $responses );
		$this->assertEquals( $userdata['apns_uuid'], $responses[0]->get_uuid() );
		$this->assertEquals( $userdata['apns_token'], $responses[0]->get_token() );

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
