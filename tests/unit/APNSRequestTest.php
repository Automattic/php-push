<?php
declare( strict_types = 1 );
class APNSRequestTest extends APNSTest {

	public function testThatGetBodyRetrievesJSONEncodedPush() {
		$push = $this->new_payload();
		$this->assertEquals( json_encode( $push ), $this->getRequest( $push )->getBody() );
	}

	public function testThatGetUrlForTokenRetrievesValidValue() {
		$token = $this->random_string();
		$configuration = $this->new_sandbox_configuration();

		$req = $this->getRequest( $this->new_payload(), $token, $this->new_metadata() );

		$this->assertEquals( APNSConfiguration::APNS_ENDPOINT_SANDBOX . $token, $req->getUrlForConfiguration( $configuration ) );
	}

	public function testThatHeadersContainsAuthorizationToken() {
		$token = $this->random_string();
		$configuration = $this->new_configuration_with_mocked_provider_token( $token );

		$headers = $this->getRequest( $this->new_payload(), $token, $this->new_metadata() )->getHeadersForConfiguration( $configuration );

		$this->assertArrayHasKey( 'authorization', $headers );
		$this->assertEquals( 'bearer ' . $token, $headers['authorization'] );
	}

	public function testThatGetHeadersContainsContentType() {
		$configuration = $this->new_configuration();

		$headers = $this->getRequest( $this->new_payload() )->getHeadersForConfiguration( $configuration );

		$this->assertArrayHasKey( 'content-type', $headers );
		$this->assertEquals( 'application/json', $headers['content-type'] );
	}

	public function testThatGetHeadersContainsValidContentLength() {
		$push = $this->new_payload();
		$configuration = $this->new_configuration();

		$headers = $this->getRequest( $push )->getHeadersForConfiguration( $configuration );

		$this->assertArrayHasKey( 'content-length', $headers );
		$this->assertEquals( strlen( json_encode( $push ) ), $headers['content-length'] );
	}

	public function testThatGetHeadersContainsAPNSExpiration() {
		$configuration = $this->new_configuration();
		$expiration = time() + 60;
		$meta = $this->new_metadata()->setExpirationTimestamp( $expiration );

		$headers = $this->getRequest( $this->new_payload(), null, $meta )->getHeadersForConfiguration( $configuration );

		$this->assertArrayHasKey( 'apns-expiration', $headers );
		$this->assertEquals( $expiration, $headers['apns-expiration'] );
	}

	public function testThatGetHeadersContainsAPNSPushType() {
		$configuration = $this->new_configuration();
		$push_type = APNSPushType::MDM;
		$meta = $this->new_metadata()->setPushType( $push_type );

		$headers = $this->getRequest( $this->new_payload(), null, $meta )->getHeadersForConfiguration( $configuration );

		$this->assertArrayHasKey( 'apns-push-type', $headers );
		$this->assertEquals( $push_type, $headers['apns-push-type'] );
	}

	public function testThatGetHeadersContainsTopic() {
		$configuration = $this->new_configuration();
		$topic = $this->random_string();
		$meta = $this->new_metadata()->setTopic( $topic );

		$headers = $this->getRequest( $this->new_payload(), null, $meta )->getHeadersForConfiguration( $configuration );

		$this->assertArrayHasKey( 'apns-topic', $headers );
		$this->assertEquals( $topic, $headers['apns-topic'] );
	}

	public function testThatGetHeadersDoesNotContainUserAgentByDefault() {
		$configuration = $this->new_configuration();
		$this->assertArrayNotHasKey( 'user-agent', $this->getRequest()->getHeadersForConfiguration( $configuration ) );
	}

	public function testThatGetHeadersContainsUserAgentIfSet() {
		$ua = $this->random_string();
		$configuration = $this->new_configuration()->setUserAgent( $ua );

		$headers = $this->getRequest( $this->new_payload(), null, $this->new_metadata() )->getHeadersForConfiguration( $configuration );

		$this->assertArrayHasKey( 'user-agent', $headers );
		$this->assertEquals( $ua, $headers['user-agent'] );
	}

	public function testThatGetHeadersDoesNotContainPriorityByDefault() {
		$configuration = $this->new_configuration();
		$this->assertArrayNotHasKey( 'apns-priority', $this->getRequest()->getHeadersForConfiguration( $configuration ) );
	}

	public function testThatGetHeadersContainsPriorityIfSet() {
		$configuration = $this->new_configuration();
		$meta = $this->new_metadata()->setLowPriority();

		$headers = $this->getRequest( $this->new_payload(), null, $meta )->getHeadersForConfiguration( $configuration );

		$this->assertArrayHasKey( 'apns-priority', $headers );
		$this->assertEquals( 5, $headers['apns-priority'] );
	}

	public function testThatGetHeadersAlwaysContainApnsIdByDefault() {
		$configuration = $this->new_configuration();
		$this->assertArrayHasKey( 'apns-id', $this->getRequest()->getHeadersForConfiguration( $configuration ) );
	}

	public function testThatGetHeadersContainsApnsIdIfSet() {
		$configuration = $this->new_configuration();
		$uuid = $this->random_uuid();
		$meta = $this->new_metadata()->setUuid( $uuid );

		$headers = $this->getRequest( $this->new_payload(), null, $meta )->getHeadersForConfiguration( $configuration );

		$this->assertArrayHasKey( 'apns-id', $headers );
		$this->assertEquals( $uuid, $headers['apns-id'] );
	}

	public function testThatGetHeadersDoesNotContainCollapseIdByDefault() {
		$configuration = $this->new_configuration();
		$this->assertArrayNotHasKey( 'apns-collapse-id', $this->getRequest()->getHeadersForConfiguration( $configuration ) );
	}

	public function testThatGetHeadersContainsCollapseIdIfSet() {
		$configuration = $this->new_configuration();
		$id = $this->random_string();
		$meta = $this->new_metadata()->setCollapseIdentifier( $id );

		$headers = $this->getRequest( $this->new_payload(), null, $meta )->getHeadersForConfiguration( $configuration );

		$this->assertArrayHasKey( 'apns-collapse-id', $headers );
		$this->assertEquals( $id, $headers['apns-collapse-id'] );
	}

	private function getRequest( $payload = null, string $token = null, ?APNSRequestMetadata $metadata = null ) {

		if ( is_null( $payload ) ) {
			$payload = $this->new_payload();
		}

		if ( is_null( $token ) ) {
			$token = $this->random_string();
		}

		if ( is_null( $metadata ) ) {
			$metadata = $this->new_metadata();
		}

		return APNSRequest::fromPayload( $payload, $token, $metadata );
	}
}
