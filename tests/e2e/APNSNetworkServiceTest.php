<?php

class APNSNetworkServiceIntegrationTest extends APNSTest {
	
	function testThatSendingAfterGoAwayFrameEmitsIsRecoverable() {

		$count = random_int(1, 1000);

		$service = (new APNSNetworkService())
			->setCertificateBundlePath( dirname( __DIR__ ). '/MockAPNSServer/test-cert.pem' )
			->setPort(8443);

		for($i = 0; $i < $count; $i++) {
			$service->enqueueRequest('https://127.0.0.1/', [], '');
		}

		$responses = $service->sendQueuedRequests();
		$this->assertCount($count, $responses);

		$this->reset_mock_server();

		for($i = 0; $i < $count; $i++) {
			$service->enqueueRequest('https://127.0.0.1/', [], '');
		}

		$responses = $service->sendQueuedRequests();
		$this->assertTrue($responses[0]->isError());
		$this->assertTrue($responses[0]->shouldRetry());
		$this->assertCount($count, $responses);

		$service->closeConnection();
	}

	private function reset_mock_server() {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://127.0.0.1/reset');
		curl_setopt( $ch, CURLOPT_PORT, 8443 );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, false );
		curl_setopt( $ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_2TLS );
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 

        curl_exec($ch);
        curl_close($ch);

	}
}
