<?php
declare( strict_types = 1 );
class APNSResponseTest extends APNSTest {

	public function testThatResponseParserReadsSuccessResponse() {
		$data     = $this->get_test_resource( '200-success-response' );
		$response = new APNSResponse( 200, $data, new APNSResponseMetrics(), $this->new_userdata() );

		$this->assertEquals( 200, $response->get_status_code() );
		$this->assertFalse( $response->is_error() );
	}

	public function testThatResponseParserReadsErrorResponse() {
		$data     = $this->get_test_resource( '400-bad-device-token-response' );
		$response = new APNSResponse( 400, $data, new APNSResponseMetrics(), $this->new_userdata() );

		$this->assertEquals( 400, $response->get_status_code() );
		$this->assertEquals( 'BadDeviceToken', $response->get_error_message() );
	}

	public function testThatResponseReadsUuidFromUserData() {
		$expected_uuid = $this->random_uuid();
		$response      = new APNSResponse( 400, '', new APNSResponseMetrics(), $this->new_userdata_with_uuid( $expected_uuid ) );
		$this->assertEquals( $expected_uuid, $response->get_uuid() );
	}

	public function testThatResponseReadsTokenFromUserData() {
		$expected_token = $this->random_uuid();
		$response       = new APNSResponse( 400, '', new APNSResponseMetrics(), $this->new_userdata_with_token( $expected_token ) );
		$this->assertEquals( $expected_token, $response->get_token() );
	}

	public function testThatResponseProvidesUserData() {
		$key   = $this->random_string();
		$value = $this->random_string();

		$response = new APNSResponse( 400, '', new APNSResponseMetrics(), array_merge( $this->new_userdata(), [ $key => $value ] ) );

		$this->assertEquals( $value, $response->get_userdata()[ $key ] );
	}

	public function testThatUnrecoverableErrorsAreCorrectlyRecognized() {
		$this->assertTrue( $this->new_apns_response( 400 )->is_unrecoverable_error() );
		$this->assertTrue( $this->new_apns_response( 403 )->is_unrecoverable_error() );
		$this->assertTrue( $this->new_apns_response( 404 )->is_unrecoverable_error() );
		$this->assertTrue( $this->new_apns_response( 405 )->is_unrecoverable_error() );
		$this->assertTrue( $this->new_apns_response( 410 )->is_unrecoverable_error() );
		$this->assertTrue( $this->new_apns_response( 413 )->is_unrecoverable_error() );
		$this->assertFalse( $this->new_apns_response( 429 )->is_unrecoverable_error() );
	}

	public function testThatUnsubscribeResponseIsCorrectlyRecognized() {
		$this->assertTrue( $this->new_apns_response( 410 )->should_unsubscribe_device() );
	}

	public function testThatRetryableResponsesAreCorrectlyRecognized() {
		$this->assertTrue( $this->new_apns_response( 429 )->should_retry() );
		$this->assertTrue( $this->new_apns_response( random_int( 500, 599 ) )->should_retry() );
		$this->assertTrue( $this->new_apns_response( 0 )->should_retry() );
	}

	public function testThatServerErrorResponsesAreCorrectlyRecognized() {
		$this->assertTrue( $this->new_apns_response( random_int( 500, 599 ) )->is_server_error() );
	}
}
