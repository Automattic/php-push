<?php
declare( strict_types = 1 );

class APNSClientTest extends APNSTest {

	public function testThatRequestsFromNetworkAreProcessed() {
		$network_service_mock = Mockery::mock( 'CurlMultiplexedNetworkService' );
		$network_service_mock
			->shouldReceive( 'enqueueRequest' )
			->once();
		$fake_responses = [
			new Response( 200, $this->new_apns_http_failure_response( 200 ), 1, 1 ),
			new Response( 400, $this->new_apns_http_failure_response( 400 ), 1, 1 ),
		];
		$network_service_mock
			->shouldReceive( 'sendQueuedRequests' )
			->andReturn( $fake_responses );
		$client = new APNSClient( $this->new_configuration(), $network_service_mock );

		$responses = $client->sendRequests( [ $this->new_request() ] );

		$this->assertEquals( 2, count( $responses ) );
		// For the purpose of this test it's enough to verify that the received
		// requests are mapped to the expected type by means of calling a
		// method on them. It's then up to the test for the type initialization
		// to verify the mapping is done correctly.
		$this->assertEquals( 200, $responses[0]->getStatusCode() );
		$this->assertEquals( 400, $responses[1]->getStatusCode() );
	}
}
