<?php
declare( strict_types = 1 );

class APNSClientTest extends APNSTest {

	public function testThatRequestsFromNetworkAreProcessed() {
		$network_service_mock = Mockery::mock( 'APNSNetworkService' );
		$network_service_mock
			->shouldReceive( 'enqueueRequest' )
			->once();
		$fake_responses = [
			new APNSResponse( 200, $this->new_apns_http_failure_response( 200 ), new APNSResponseMetrics( 1, 1 ) ),
			new APNSResponse( 400, $this->new_apns_http_failure_response( 400 ), new APNSResponseMetrics( 1, 1 ) ),
		];
		$network_service_mock
			->shouldReceive( 'sendQueuedRequests' )
			->andReturn( $fake_responses );
		$client = APNSClient::withConfiguration( $this->new_configuration(), $network_service_mock );

		$responses = $client->sendRequests( [ $this->new_request() ] );

		$this->assertEquals( count( $fake_responses ), count( $responses ) );
		// For the purpose of this test it's enough to verify that the received
		// requests are mapped to the expected type by means of calling a
		// method on them. It's then up to the test for the type initialization
		// to verify the mapping is done correctly.
		$this->assertEquals( $fake_responses[0]->getStatusCode(), $responses[0]->getStatusCode() );
		$this->assertEquals( $fake_responses[1]->getStatusCode(), $responses[1]->getStatusCode() );
	}
}
