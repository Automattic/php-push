<?php
declare( strict_types = 1 );
class APNSRequestTest extends APNSTest {

	public function testThatGetBodyRetrievesJSONEncodedPush() {
		$push = $this->new_payload();
		$this->assertEquals( json_encode( $push ), $this->getRequest( $push )->getBody() );
	}

	public function testThatGetUrlForTokenRetrievesValidValue() {
		$endpoint = $this->random_string();
		$token = $this->random_string();
		$req = $this->getRequest( $this->new_payload(), $this->new_metadata(), $this->new_configuration_with_mocked_endpoint( $endpoint ) );
		$this->assertEquals( $endpoint . $token, $req->getUrlForToken( $token ) );
	}

	public function testThatHeadersContainsAuthorizationToken() {
		$token = $this->random_string();
		$headers = $this->getRequest( $this->new_payload(), $this->new_metadata(), $this->new_configuration_with_mocked_provider_token( $token ) )
			->getHeaders();

		$this->assertArrayHasKey( 'authorization', $headers );
		$this->assertEquals( 'bearer ' . $token, $headers['authorization'] );
	}

	public function testThatGetHeadersContainsContentType() {
		$headers = $this->getRequest( $this->new_payload() )->getHeaders();
		$this->assertArrayHasKey( 'content-type', $headers );
		$this->assertEquals( 'application/json', $headers['content-type'] );
	}

	public function testThatGetHeadersContainsValidContentLength() {
		$push = $this->new_payload();
		$headers = $this->getRequest( $push )->getHeaders();
		$this->assertArrayHasKey( 'content-length', $headers );
		$this->assertEquals( strlen( json_encode( $push ) ), $headers['content-length'] );
	}

	public function testThatGetHeadersContainsAPNSExpiration() {
		$expiration = time() + 60;
		$meta = $this->new_metadata()->setExpirationTimestamp( $expiration );
		$headers = $this->getRequest( $this->new_payload(), $meta )->getHeaders();
		$this->assertArrayHasKey( 'apns-expiration', $headers );
		$this->assertEquals( $expiration, $headers['apns-expiration'] );
	}

	public function testThatGetHeadersContainsAPNSPushType() {
		$push_type = APNSPushType::MDM;
		$meta = $this->new_metadata()->setPushType( $push_type );
		$headers = $this->getRequest( $this->new_payload(), $meta )->getHeaders();
		$this->assertArrayHasKey( 'apns-push-type', $headers );
		$this->assertEquals( $push_type, $headers['apns-push-type'] );
	}

	public function testThatGetHeadersContainsTopic() {
		$topic = $this->random_string();
		$meta = $this->new_metadata()->setTopic( $topic );
		$headers = $this->getRequest( $this->new_payload(), $meta )->getHeaders();
		$this->assertArrayHasKey( 'apns-topic', $headers );
		$this->assertEquals( $topic, $headers['apns-topic'] );
	}

	public function testThatGetHeadersDoesNotContainUserAgentByDefault() {
		$this->assertArrayNotHasKey( 'user-agent', $this->getRequest()->getHeaders() );
	}

	public function testThatGetHeadersContainsUserAgentIfSet() {
		$ua = $this->random_string();
		$config = $this->new_configuration()->setUserAgent( $ua );
		$headers = $this->getRequest( $this->new_payload(), $this->new_metadata(), $config )->getHeaders();
		$this->assertArrayHasKey( 'user-agent', $headers );
		$this->assertEquals( $ua, $headers['user-agent'] );
	}

	public function testThatGetHeadersDoesNotContainPriorityByDefault() {
		$this->assertArrayNotHasKey( 'apns-priority', $this->getRequest()->getHeaders() );
	}

	public function testThatGetHeadersContainsPriorityIfSet() {
		$meta = $this->new_metadata()->setLowPriority();
		$headers = $this->getRequest( $this->new_payload(), $meta )->getHeaders();
		$this->assertArrayHasKey( 'apns-priority', $headers );
		$this->assertEquals( 5, $headers['apns-priority'] );
	}

	public function testThatGetHeadersDoesNotContainApnsIdByDefault() {
		$this->assertArrayNotHasKey( 'apns-id', $this->getRequest()->getHeaders() );
	}

	public function testThatGetHeadersContainsApnsIdIfSet() {
		$uuid = $this->random_uuid();
		$meta = $this->new_metadata()->setUuid( $uuid );
		$headers = $this->getRequest( $this->new_payload(), $meta )->getHeaders();
		$this->assertArrayHasKey( 'apns-id', $headers );
		$this->assertEquals( $uuid, $headers['apns-id'] );
	}

	public function testThatGetHeadersDoesNotContainCollapseIdByDefault() {
		$this->assertArrayNotHasKey( 'apns-collapse-id', $this->getRequest()->getHeaders() );
	}

	public function testThatGetHeadersContainsCollapseIdIfSet() {
		$id = $this->random_string();
		$meta = $this->new_metadata()->setCollapseIdentifier( $id );
		$headers = $this->getRequest( $this->new_payload(), $meta )->getHeaders();
		$this->assertArrayHasKey( 'apns-collapse-id', $headers );
		$this->assertEquals( $id, $headers['apns-collapse-id'] );
	}

	private function getRequest( $push = null, ?APNSRequestMetadata $metadata = null, ?APNSConfiguration $config = null ) {

		if ( is_null( $push ) ) {
			$push = $this->new_payload();
		}

		if ( is_null( $metadata ) ) {
			$metadata = $this->new_metadata();
		}

		if ( is_null( $config ) ) {
			$config = $this->new_configuration();
		}

		return new APNSRequest( $push, $metadata, $config );
	}
}
