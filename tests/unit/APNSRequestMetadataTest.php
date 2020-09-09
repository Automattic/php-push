<?php
declare( strict_types = 1 );
class APNSRequestMetadataTest extends APNSTest {

	public function testThatInitializerStoresTopic() {
		$topic = $this->random_string();
		$meta  = new APNSRequestMetadata( $topic );
		$this->assertEquals( $topic, $meta->get_topic() );
	}

	public function testThatConvenienceInitializerStoresTopic() {
		$topic = $this->random_string();
		$this->assertEquals( $topic, APNSRequestMetadata::with_topic( $topic )->get_topic() );
	}

	public function testThatTopicSetterWorks() {
		$topic = $this->random_string();
		$meta  = $this->new_metadata()->set_topic( $topic );
		$this->assertEquals( $topic, $meta->get_topic() );
	}

	public function testThatTopicSetterThrowsForEmptyTopic() {
		$topic = ' ';
		$this->expectException( InvalidArgumentException::class );
		$this->new_metadata()->set_topic( $topic );
	}

	public function testThatDefaultPushTypeIsAlert() {
		$this->assertEquals( APNSPushType::ALERT, $this->new_metadata()->get_push_type() );
	}

	public function testThatPushTypeSetterWorks() {
		$meta = $this->new_metadata()->set_push_type( APNSPushType::BACKGROUND );
		$this->assertEquals( APNSPushType::BACKGROUND, $meta->get_push_type() );
	}

	public function testThatPushTypeSetterAssertsForInvalidPushType() {
		$this->expectException( InvalidArgumentException::class );
		$this->new_metadata()->set_push_type( $this->random_string() );
	}

	public function testThatDefaultExpirationTimeIsZero() {
		$this->assertEquals( 0, $this->new_metadata()->get_expiration_timestamp() );
	}

	public function testThatExpirationTimestampSetterWorks() {
		$timestamp = strtotime( 'now' ) + 100;
		$meta      = $this->new_metadata()->set_expiration_timestamp( $timestamp );
		$this->assertEquals( $timestamp, $meta->get_expiration_timestamp() );
	}

	public function testThatDefaultPriorityIsImmediate() {
		$this->assertEquals( APNSPriority::IMMEDIATE, $this->new_metadata()->get_priority() );
	}

	public function testThatSettingLowPriorityWorks() {
		$meta = $this->new_metadata()->set_low_priority();
		$this->assertEquals( APNSPriority::THROTTLED, $meta->get_priority() );
	}

	public function testThatSettingNormalPriorityWorks() {
		$meta = $this->new_metadata()->set_low_priority()->set_normal_priority();
		$this->assertEquals( APNSPriority::IMMEDIATE, $meta->get_priority() );
	}

	public function testThatDefaultCollapseIdentifierIsNull() {
		$this->assertNull( $this->new_metadata()->get_collapse_identifier() );
	}

	public function testThatCollapseIdentifierSetterWorks() {
		$identifier = $this->random_string( 64 );
		$meta       = $this->new_metadata()->set_collapse_identifier( $identifier );
		$this->assertEquals( $identifier, $meta->get_collapse_identifier() );
	}

	public function testThatCollapseIdentifierSetterThrowsForInvalidIdentifier() {
		$identifier = $this->random_string( 65 );
		$this->expectException( InvalidArgumentException::class );
		$this->new_metadata()->set_collapse_identifier( $identifier );
	}

	public function testThatUuidSetterWorks() {
		$uuid = $this->random_uuid();
		$meta = $this->new_metadata()->set_uuid( $uuid );
		$this->assertEquals( $uuid, $meta->get_uuid() );
	}

	public function testThatMetadataSerializationIncludesTopic() {
		$topic   = $this->random_string();
		$encoded = $this->from_json( $this->new_metadata( $topic )->to_json() );
		$this->assertEquals( $topic, $encoded->topic );
	}

	public function testThatMetadataSerializationIncludesUuid() {
		$uuid    = $this->random_string();
		$encoded = $this->from_json( $this->new_metadata( $this->random_string(), $uuid )->to_json() );
		$this->assertEquals( $uuid, $encoded->uuid );
	}

	public function testThatMetadataSerializationDoesNotIncludePushTypeByDefault() {
		$encoded = $this->from_json( $this->new_metadata()->to_json() );
		$this->assertFalse( property_exists( $encoded, 'push_type' ) );
	}

	public function testThatMetadataSerializationIncludesPushTypeIfSpecified() {
		$push_type = APNSPushType::MDM;
		$encoded   = $this->from_json( $this->new_metadata()->set_push_type( $push_type )->to_json() );
		$this->assertEquals( $push_type, $encoded->push_type );
	}

	public function testThatMetadataSerializationDoesNotIncludeExpirationTimestampByDefault() {
		$encoded = $this->from_json( $this->new_metadata()->to_json() );
		$this->assertFalse( property_exists( $encoded, 'expiration_timestamp' ) );
	}

	public function testThatMetadataSerializationIncludesExpirationTimestampIfSpecified() {
		$expires_at = random_int( 1, time() );
		$encoded    = $this->from_json( $this->new_metadata()->set_expiration_timestamp( $expires_at )->to_json() );
		$this->assertEquals( $expires_at, $encoded->expiration_timestamp );
	}

	public function testThatMetadataSerializationDoesNotIncludePriorityByDefault() {
		$encoded = $this->from_json( $this->new_metadata()->to_json() );
		$this->assertFalse( property_exists( $encoded, 'priority' ) );
	}

	public function testThatMetadataSerializationIncludesPriorityIfSpecified() {
		$encoded = $this->from_json( $this->new_metadata()->set_low_priority()->to_json() );
		$this->assertEquals( APNSPriority::THROTTLED, $encoded->priority );
	}

	public function testThatMetadataSerializationDoesNotIncludeCollapseIdentifierByDefault() {
		$encoded = $this->from_json( $this->new_metadata()->to_json() );
		$this->assertFalse( property_exists( $encoded, 'collapse_identifier' ) );
	}

	public function testThatMetadataSerializationIncludesCollapseIdentifierIfSpecified() {
		$identifier = $this->random_string();
		$encoded    = $this->from_json( $this->new_metadata()->set_collapse_identifier( $identifier )->to_json() );
		$this->assertEquals( $identifier, $encoded->collapse_identifier );
	}

	public function testThatMetadataDeserializationIncludesTopic() {
		$topic = $this->random_string();
		$meta  = APNSRequestMetadata::from_json( $this->new_metadata( $topic )->to_json() );
		$this->assertEquals( $topic, $meta->get_topic() );
	}

	public function testThatMetadataDeserializationIncludesUuid() {
		$uuid = $this->random_uuid();
		$meta = APNSRequestMetadata::from_json( $this->new_metadata( null, $uuid )->to_json() );
		$this->assertEquals( $uuid, $meta->get_uuid() );
	}

	public function testThatMetadataDeserializationThrowsForMissingTopic() {
		$json = $this->json_without( $this->new_metadata()->to_json(), 'topic' );
		$this->expectException( InvalidArgumentException::class );
		APNSRequestMetadata::from_json( $json );
	}

	public function testThatMetadataDeserializationThrowsForMissingUuid() {
		$json = $this->json_without( $this->new_metadata()->to_json(), 'uuid' );
		$this->expectException( InvalidArgumentException::class );
		APNSRequestMetadata::from_json( $json );
	}

	public function testThatMetadataDeserializationIncludesPushTypeIfPresent() {
		$push_type = APNSPushType::MDM;
		$json      = $this->json_adding( $this->new_metadata()->to_json(), 'push_type', $push_type );
		$meta      = APNSRequestMetadata::from_json( $json );
		$this->assertEquals( $push_type, $meta->get_push_type() );
	}

	public function testThatMetadataDeserializationIncludesExpirationTimestampIfPresent() {
		$expiration_timestamp = time();
		$json                 = $this->json_adding( $this->new_metadata()->to_json(), 'expiration_timestamp', $expiration_timestamp );
		$meta                 = APNSRequestMetadata::from_json( $json );
		$this->assertEquals( $expiration_timestamp, $meta->get_expiration_timestamp() );
	}

	public function testThatMetadataDeserializationIncludesLowPriorityIfPresent() {
		$priority = APNSPriority::THROTTLED;
		$json     = $this->json_adding( $this->new_metadata()->to_json(), 'priority', $priority );
		$meta     = APNSRequestMetadata::from_json( $json );
		$this->assertEquals( $priority, $meta->get_priority() );
	}

	public function testThatMetadataDeserializationIncludesNormalPriorityIfPresent() {
		$priority = APNSPriority::IMMEDIATE;
		$json     = $this->json_adding( $this->new_metadata()->to_json(), 'priority', $priority );
		$meta     = APNSRequestMetadata::from_json( $json );
		$this->assertEquals( $priority, $meta->get_priority() );
	}

	public function testThatMetadataDeserializationThrowsForInvalidPriority() {
		$json = $this->json_adding( $this->new_metadata()->to_json(), 'priority', 0 );
		$this->expectException( InvalidArgumentException::class );
		APNSRequestMetadata::from_json( $json );
	}

	public function testThatMetadataDeserializationIncludesCollapseIdentifierIfPresent() {
		$collapse_identifier = $this->random_string();
		$json                = $this->json_adding( $this->new_metadata()->to_json(), 'collapse_identifier', $collapse_identifier );
		$meta                = APNSRequestMetadata::from_json( $json );
		$this->assertEquals( $collapse_identifier, $meta->get_collapse_identifier() );
	}
}
