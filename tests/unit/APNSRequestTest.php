<?php
declare( strict_types = 1 );
class APNSRequestTest extends APNSTest {

	public function testRequestInstantiationFromString() {
		$message = $this->random_string();
		$token   = $this->random_string();

		$request = APNSRequest::from_string( $message, $token, $this->new_metadata() );
		$this->assertEquals( $message, $this->decode( $request->get_body() )->aps->alert );
	}

	public function testRequestInstantiationFromPayload() {
		$message = $this->random_string();
		$token   = $this->random_string();

		$request = APNSRequest::from_payload( APNSPayload::from_string( $message ), $token, $this->new_metadata() );
		$this->assertEquals( $message, $this->decode( $request->get_body() )->aps->alert );
	}

	public function testThatUuidCannotBeOverwrittenWithCustomUserData() {
		$valid_uuid = $this->random_uuid();
		$metadata   = $this->new_metadata( 'topic', $valid_uuid );

		$request = APNSRequest::from_string( '', '', $metadata, $this->new_userdata_with_uuid( $this->random_uuid() ) );

		$this->assertEquals( $valid_uuid, $request->get_uuid() );
	}

	public function testThatTokenCannotBeOverwrittenWithCustomUserData() {
		$valid_token = $this->random_uuid();

		$request = APNSRequest::from_string( 'message', $valid_token, $this->new_metadata(), $this->new_userdata_with_token( $this->random_uuid() ) );

		$this->assertEquals( $valid_token, $request->get_token() );
	}

	public function testThatGetTokenRetrievesToken() {
		$message = $this->random_string();
		$token   = $this->random_string();

		$request = APNSRequest::from_string( $message, $token, $this->new_metadata() );
		$this->assertEquals( $token, $request->get_token() );
	}

	public function testThatGetBodyRetrievesJSONEncodedPush() {
		$push = $this->new_payload();
		$this->assertEquals( $push->to_json(), $this->new_request( $push )->get_body() );
	}

	public function testThatGetUuidRetrievesUuid() {
		$uuid = $this->random_uuid();

		$request = APNSRequest::from_string( '', '', $this->new_metadata( null, $uuid ) );
		$this->assertEquals( $uuid, $request->get_uuid() );
	}

	public function testThatGetUrlForTokenRetrievesValidValue() {
		$token         = $this->random_string();
		$configuration = $this->new_sandbox_configuration();

		$req = $this->new_request( $this->new_payload(), $token, $this->new_metadata() );

		$this->assertEquals( APNSConfiguration::APNS_ENDPOINT_SANDBOX . $token, $req->get_url_for_configuration( $configuration ) );
	}

	public function testThatHeadersContainsAuthorizationToken() {
		$token         = $this->random_string();
		$configuration = $this->new_configuration_with_mocked_provider_token( $token );

		$headers = $this->new_request( $this->new_payload(), $token, $this->new_metadata() )->get_headers_for_configuration( $configuration );

		$this->assertArrayHasKey( 'authorization', $headers );
		$this->assertEquals( 'bearer ' . $token, $headers['authorization'] );
	}

	public function testThatGetHeadersContainsContentType() {
		$configuration = $this->new_configuration();

		$headers = $this->new_request( $this->new_payload() )->get_headers_for_configuration( $configuration );

		$this->assertArrayHasKey( 'content-type', $headers );
		$this->assertEquals( 'application/json', $headers['content-type'] );
	}

	public function testThatGetHeadersContainsValidContentLength() {
		$push          = $this->new_payload();
		$configuration = $this->new_configuration();

		$headers = $this->new_request( $push )->get_headers_for_configuration( $configuration );

		$this->assertArrayHasKey( 'content-length', $headers );
		$this->assertEquals( strlen( $push->to_json() ), $headers['content-length'] );
	}

	public function testThatGetHeadersContainsAPNSExpiration() {
		$configuration = $this->new_configuration();
		$expiration    = time() + 60;
		$meta          = $this->new_metadata()->set_expiration_timestamp( $expiration );

		$headers = $this->new_request( $this->new_payload(), null, $meta )->get_headers_for_configuration( $configuration );

		$this->assertArrayHasKey( 'apns-expiration', $headers );
		$this->assertEquals( $expiration, $headers['apns-expiration'] );
	}

	public function testThatGetHeadersContainsAPNSPushType() {
		$configuration = $this->new_configuration();
		$push_type     = APNSPushType::MDM;
		$meta          = $this->new_metadata()->set_push_type( $push_type );

		$headers = $this->new_request( $this->new_payload(), null, $meta )->get_headers_for_configuration( $configuration );

		$this->assertArrayHasKey( 'apns-push-type', $headers );
		$this->assertEquals( $push_type, $headers['apns-push-type'] );
	}

	public function testThatGetHeadersContainsTopic() {
		$configuration = $this->new_configuration();
		$topic         = $this->random_string();
		$meta          = $this->new_metadata()->set_topic( $topic );

		$headers = $this->new_request( $this->new_payload(), null, $meta )->get_headers_for_configuration( $configuration );

		$this->assertArrayHasKey( 'apns-topic', $headers );
		$this->assertEquals( $topic, $headers['apns-topic'] );
	}

	public function testThatGetHeadersDoesNotContainUserAgentByDefault() {
		$configuration = $this->new_configuration();
		$this->assertArrayNotHasKey( 'user-agent', $this->new_request()->get_headers_for_configuration( $configuration ) );
	}

	public function testThatGetHeadersContainsUserAgentIfSet() {
		$ua            = $this->random_string();
		$configuration = $this->new_configuration()->set_user_agent( $ua );

		$headers = $this->new_request( $this->new_payload(), null, $this->new_metadata() )->get_headers_for_configuration( $configuration );

		$this->assertArrayHasKey( 'user-agent', $headers );
		$this->assertEquals( $ua, $headers['user-agent'] );
	}

	public function testThatGetHeadersDoesNotContainPriorityByDefault() {
		$configuration = $this->new_configuration();
		$this->assertArrayNotHasKey( 'apns-priority', $this->new_request()->get_headers_for_configuration( $configuration ) );
	}

	public function testThatGetHeadersContainsPriorityIfSet() {
		$configuration = $this->new_configuration();
		$meta          = $this->new_metadata()->set_low_priority();

		$headers = $this->new_request( $this->new_payload(), null, $meta )->get_headers_for_configuration( $configuration );

		$this->assertArrayHasKey( 'apns-priority', $headers );
		$this->assertEquals( 5, $headers['apns-priority'] );
	}

	public function testThatGetHeadersAlwaysContainApnsIdByDefault() {
		$configuration = $this->new_configuration();
		$this->assertArrayHasKey( 'apns-id', $this->new_request()->get_headers_for_configuration( $configuration ) );
	}

	public function testThatGetHeadersContainsApnsIdIfSet() {
		$configuration = $this->new_configuration();
		$uuid          = $this->random_uuid();
		$meta          = $this->new_metadata()->set_uuid( $uuid );

		$headers = $this->new_request( $this->new_payload(), null, $meta )->get_headers_for_configuration( $configuration );

		$this->assertArrayHasKey( 'apns-id', $headers );
		$this->assertEquals( $uuid, $headers['apns-id'] );
	}

	public function testThatGetHeadersDoesNotContainCollapseIdByDefault() {
		$configuration = $this->new_configuration();
		$this->assertArrayNotHasKey( 'apns-collapse-id', $this->new_request()->get_headers_for_configuration( $configuration ) );
	}

	public function testThatGetHeadersContainsCollapseIdIfSet() {
		$configuration = $this->new_configuration();
		$id            = $this->random_string();
		$meta          = $this->new_metadata()->set_collapse_identifier( $id );

		$headers = $this->new_request( $this->new_payload(), null, $meta )->get_headers_for_configuration( $configuration );

		$this->assertArrayHasKey( 'apns-collapse-id', $headers );
		$this->assertEquals( $id, $headers['apns-collapse-id'] );
	}

	public function testThatRequestSerializationIncludesPayload() {
		$payload = $this->new_payload();
		$request = $this->decode( $this->new_request( $payload )->to_json() );

		$this->assertEquals( $payload->to_json(), $request->payload );
	}

	public function testThatRequestSerializationIncludesMetadata() {
		$metadata = $this->new_metadata();
		$request  = $this->decode( $this->new_request_from_metadata( $metadata )->to_json() );

		$this->assertEquals( $metadata->to_json(), $request->metadata );
	}

	public function testThatRequestSerializationIncludesToken() {
		$token   = $this->random_string();
		$request = $this->new_request_from_token( $token );
		$this->assertEquals( $token, $request->get_token() );
	}

	public function testThatRequestDeserializationIncludesPayload() {
		$payload = $this->new_payload();
		$request = APNSRequest::from_json( $this->new_request( $payload )->to_json() );
		$this->assertEquals( $payload->to_json(), $request->get_body() );
	}

	public function testThatRequestDeserializationIncludesToken() {
		$token   = $this->random_string();
		$request = APNSRequest::from_json( $this->new_request_from_token( $token )->to_json() );
		$this->assertEquals( $token, $request->get_token() );
	}

	public function testThatRequestDeserializationIncludesMetadata() {
		$meta    = $this->new_metadata();
		$request = APNSRequest::from_json( $this->new_request_from_metadata( $meta )->to_json() );
		$this->assertEquals( $meta, $request->get_metadata() );
	}

	public function testThatRequestDeserializationThrowsForMissingPayload() {
		$json = $this->json_without( $this->new_request()->to_json(), 'payload' );
		$this->expectException( InvalidArgumentException::class );
		APNSRequest::from_json( $json );
	}

	public function testThatRequestDeserializationThrowsForMissingToken() {
		$json = $this->json_without( $this->new_request()->to_json(), 'token' );
		$this->expectException( InvalidArgumentException::class );
		APNSRequest::from_json( $json );
	}

	public function testThatRequestDeserializationThrowsForMissingMetadata() {
		$json = $this->json_without( $this->new_request()->to_json(), 'metadata' );
		$this->expectException( InvalidArgumentException::class );
		APNSRequest::from_json( $json );
	}
}
