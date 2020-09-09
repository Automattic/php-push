<?php
declare( strict_types = 1 );
class APNSResponseTest extends APNSTest {

	public function testThatResponseParserReadsSuccessResponse() {
		$data     = $this->get_test_resource( '200-success-response' );
		$response = new APNSResponse( 200, $data, new APNSResponseMetrics() );

		$this->assertEquals( '8FE746FE-1112-2966-3590-2DC3F038536B', $response->get_uuid() );
		$this->assertEquals( 200, $response->get_status_code() );
		$this->assertFalse( $response->is_error() );
	}

	public function testThatResponseParserReadsErrorResponse() {
		$data     = $this->get_test_resource( '400-bad-device-token-response' );
		$response = new APNSResponse( 400, $data, new APNSResponseMetrics() );

		$this->assertEquals( '8FE746FE-1112-2966-3590-2DC3F038536B', $response->get_uuid() );
		$this->assertEquals( 400, $response->get_status_code() );
		$this->assertEquals( 'BadDeviceToken', $response->get_error_message() );
	}

	public function testThatUnrecoverableErrorsAreCorrectlyRecognized() {
		$this->assertTrue( $this->makeAPNSResponseFor( 400 )->is_unrecoverable_error() );
		$this->assertTrue( $this->makeAPNSResponseFor( 403 )->is_unrecoverable_error() );
		$this->assertTrue( $this->makeAPNSResponseFor( 404 )->is_unrecoverable_error() );
		$this->assertTrue( $this->makeAPNSResponseFor( 405 )->is_unrecoverable_error() );
		$this->assertTrue( $this->makeAPNSResponseFor( 410 )->is_unrecoverable_error() );
		$this->assertTrue( $this->makeAPNSResponseFor( 413 )->is_unrecoverable_error() );
		$this->assertFalse( $this->makeAPNSResponseFor( 429 )->is_unrecoverable_error() );
	}

	public function testThatUnsubscribeResponseIsCorrectlyRecognized() {
		$this->assertTrue( $this->makeAPNSResponseFor( 410 )->should_unsubscribe_device() );
	}

	public function testThatRetryableResponsesAreCorrectlyRecognized() {
		$this->assertTrue( $this->makeAPNSResponseFor( 429 )->should_retry() );
		$this->assertTrue( $this->makeAPNSResponseFor( random_int( 500, 599 ) )->should_retry() );
		$this->assertTrue( $this->makeAPNSResponseFor( 0 )->should_retry() );
	}

	public function testThatServerErrorResponsesAreCorrectlyRecognized() {
		$this->assertTrue( $this->makeAPNSResponseFor( random_int( 500, 599 ) )->is_server_error() );
	}

	private function makeAPNSResponseFor( int $status_code ): APNSResponse {
		return new APNSResponse(
			$status_code,
			$this->new_apns_http_failure_response( $status_code ),
			new APNSResponseMetrics()
		);
	}
}
