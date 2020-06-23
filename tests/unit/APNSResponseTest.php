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
}
