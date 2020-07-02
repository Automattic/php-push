<?php
declare( strict_types = 1 );
class APNSResponseTest extends APNSTest {

	public function testThatResponseParserReadsSuccessResponse() {
		$data = $this->get_test_resource( '200-success-response' );
		$response = new APNSResponse( 200, $data, new APNSResponseMetrics() );

		$this->assertEquals( '8FE746FE-1112-2966-3590-2DC3F038536B', $response->getUuid() );
		$this->assertEquals( 200, $response->getStatusCode() );
		$this->assertFalse( $response->isError() );
	}

	public function testThatResponseParserReadsErrorResponse() {
		$data = $this->get_test_resource( '400-bad-device-token-response' );
		$response = new APNSResponse( 400, $data, new APNSResponseMetrics() );

		$this->assertEquals( '8FE746FE-1112-2966-3590-2DC3F038536B', $response->getUuid() );
		$this->assertEquals( 400, $response->getStatusCode() );
		$this->assertEquals( 'BadDeviceToken', $response->getErrorMessage() );
	}

	public function testThatUnrecoverableErrorsAreCorrectlyRecognized() {
		$this->assertTrue( $this->makeAPNSResponseFor( 400 )->isUnrecoverableError() );
		$this->assertTrue( $this->makeAPNSResponseFor( 403 )->isUnrecoverableError() );
		$this->assertTrue( $this->makeAPNSResponseFor( 404 )->isUnrecoverableError() );
		$this->assertTrue( $this->makeAPNSResponseFor( 405 )->isUnrecoverableError() );
		$this->assertTrue( $this->makeAPNSResponseFor( 410 )->isUnrecoverableError() );
		$this->assertTrue( $this->makeAPNSResponseFor( 413 )->isUnrecoverableError() );
		$this->assertFalse( $this->makeAPNSResponseFor( 429 )->isUnrecoverableError() );
	}

	private function makeAPNSResponseFor( int $status_code ) {
		return new APNSResponse(
			$status_code,
			$this->fakeAPNSHTTPFailureResponse( $status_code, 'not read here' ),
			new APNSResponseMetrics()
		);
	}

	private function fakeAPNSHTTPFailureResponse( int $status_code, string $reason ): string {
		return <<<TEXT
HTTP/2 $status_code
apns-id: 8FE746FE-1112-2966-3590-2DC3F038536B

{"reason":"$reason"}
TEXT;
	}
}
