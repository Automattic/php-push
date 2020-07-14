<?php
declare( strict_types = 1 );

class APNSClientTest extends APNSTest {

	public function testThatSendingRequestEnqueuesIt() {
		$network_service_mock = Mockery::spy( 'MultiplexedNetworkService' );
		$client = new APNSClient( $this->new_configuration(), $network_service_mock );

		$client->sendRequests( [ $this->new_request() ] );

		$network_service_mock->shouldHaveReceived( 'enqueueRequest' )->once();
	}
}
