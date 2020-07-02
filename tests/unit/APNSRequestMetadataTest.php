<?php
declare( strict_types = 1 );
class APNSRequestMetadataTest extends APNSTest {

	public function testThatInitializerStoresTopic() {
		$topic = $this->random_string();
		$meta = new APNSRequestMetadata( $topic );
		$this->assertEquals( $topic, $meta->getTopic() );
	}

	public function testThatTopicSetterWorks() {
		$topic = $this->random_string();
		$meta = $this->new_metadata()->setTopic( $topic );
		$this->assertEquals( $topic, $meta->getTopic() );
	}

	public function testThatTopicSetterThrowsForEmptyTopic() {
		$topic = ' ';
		$this->expectException( InvalidArgumentException::class );
		$this->new_metadata()->setTopic( $topic );
	}

	public function testThatDefaultPushTypeIsAlert() {
		$this->assertEquals( APNSPushType::ALERT, $this->new_metadata()->getPushType() );
	}

	public function testThatPushTypeSetterWorks() {
		$meta = $this->new_metadata()->setPushType( APNSPushType::BACKGROUND );
		$this->assertEquals( APNSPushType::BACKGROUND, $meta->getPushType() );
	}

	public function testThatPushTypeSetterAssertsForInvalidPushType() {
		$this->expectException( InvalidArgumentException::class );
		$this->new_metadata()->setPushType( $this->random_string() );
	}

	public function testThatDefaultExpirationTimeIsZero() {
		$this->assertEquals( 0, $this->new_metadata()->getExpirationTimestamp() );
	}

	public function testThatExpirationTimestampSetterWorks() {
		$timestamp = strtotime( 'now' ) + 100;
		$meta = $this->new_metadata()->setExpirationTimestamp( $timestamp );
		$this->assertEquals( $timestamp, $meta->getExpirationTimestamp() );
	}

	public function testThatDefaultPriorityIsImmediate() {
		$this->assertEquals( APNSPriority::IMMEDIATE, $this->new_metadata()->getPriority() );
	}

	public function testThatSettingLowPriorityWorks() {
		$meta = $this->new_metadata()->setLowPriority();
		$this->assertEquals( APNSPriority::THROTTLED, $meta->getPriority() );
	}

	public function testThatSettingNormalPriorityWorks() {
		$meta = $this->new_metadata()->setLowPriority()->setNormalPriority();
		$this->assertEquals( APNSPriority::IMMEDIATE, $meta->getPriority() );
	}

	public function testThatDefaultCollapseIdentifierIsNull() {
		$this->assertNull( $this->new_metadata()->getCollapseIdentifier() );
	}

	public function testThatCollapseIdentifierSetterWorks() {
		$identifier = $this->random_string( 64 );
		$meta = $this->new_metadata()->setCollapseIdentifier( $identifier );
		$this->assertEquals( $identifier, $meta->getCollapseIdentifier() );
	}

	public function testThatCollapseIdentifierSetterThrowsForInvalidIdentifier() {
		$identifier = $this->random_string( 65 );
		$this->expectException( InvalidArgumentException::class );
		$this->new_metadata()->setCollapseIdentifier( $identifier );
	}

	public function testThatUuidSetterWorks() {
		$uuid = $this->random_uuid();
		$meta = $this->new_metadata()->setUuid( $uuid );
		$this->assertEquals( $uuid, $meta->getUuid() );
	}

	public function testThatMetadataSerializationIncludesTopic() {
		$topic = $this->random_string();
		$encoded = $this->from_json( $this->new_metadata( $topic )->toJSON() );
		$this->assertEquals( $topic, $encoded->topic );
	}

	public function testThatMetadataSerializationIncludesUuid() {
		$uuid = $this->random_string();
		$encoded = $this->from_json( $this->new_metadata( $this->random_string(), $uuid )->toJSON() );
		$this->assertEquals( $uuid, $encoded->uuid );
	}

	public function testThatMetadataSerializationDoesNotIncludePushTypeByDefault() {
		$encoded = $this->from_json( $this->new_metadata()->toJSON() );
		$this->assertFalse( property_exists( $encoded, 'push_type' ) );
	}

	public function testThatMetadataSerializationIncludesPushTypeIfSpecified() {
		$push_type = APNSPushType::MDM;
		$encoded = $this->from_json( $this->new_metadata()->setPushType( $push_type )->toJSON() );
		$this->assertEquals( $push_type, $encoded->push_type );
	}

	public function testThatMetadataSerializationDoesNotIncludeExpirationTimestampByDefault() {
		$encoded = $this->from_json( $this->new_metadata()->toJSON() );
		$this->assertFalse( property_exists( $encoded, 'expiration_timestamp' ) );
	}

	public function testThatMetadataSerializationIncludesExpirationTimestampIfSpecified() {
		$expires_at = random_int( 1, time() );
		$encoded = $this->from_json( $this->new_metadata()->setExpirationTimestamp( $expires_at )->toJSON() );
		$this->assertEquals( $expires_at, $encoded->expiration_timestamp );
	}

	public function testThatMetadataSerializationDoesNotIncludePriorityByDefault() {
		$encoded = $this->from_json( $this->new_metadata()->toJSON() );
		$this->assertFalse( property_exists( $encoded, 'priority' ) );
	}

	public function testThatMetadataSerializationIncludesPriorityIfSpecified() {
		$encoded = $this->from_json( $this->new_metadata()->setLowPriority()->toJSON() );
		$this->assertEquals( APNSPriority::THROTTLED, $encoded->priority );
	}

	public function testThatMetadataSerializationDoesNotIncludeCollapseIdentifierByDefault() {
		$encoded = $this->from_json( $this->new_metadata()->toJSON() );
		$this->assertFalse( property_exists( $encoded, 'collapse_identifier' ) );
	}

	public function testThatMetadataSerializationIncludesCollapseIdentifierIfSpecified() {
		$identifier = $this->random_string();
		$encoded = $this->from_json( $this->new_metadata()->setCollapseIdentifier( $identifier )->toJSON() );
		$this->assertEquals( $identifier, $encoded->collapse_identifier );
	}

	public function testThatMetadataDeserializationIncludesTopic() {
		$topic = $this->random_string();
		$meta = APNSRequestMetadata::fromJSON( $this->new_metadata( $topic )->toJSON() );
		$this->assertEquals( $topic, $meta->getTopic() );
	}

	public function testThatMetadataDeserializationIncludesUuid() {
		$uuid = $this->random_uuid();
		$meta = APNSRequestMetadata::fromJSON( $this->new_metadata( null, $uuid )->toJSON() );
		$this->assertEquals( $uuid, $meta->getUuid() );
	}

	public function testThatMetadataDeserializationThrowsForMissingTopic() {
		$json = $this->json_without( $this->new_metadata()->toJSON(), 'topic' );
		$this->expectException( InvalidArgumentException::class );
		APNSRequestMetadata::fromJSON( $json );
	}

	public function testThatMetadataDeserializationThrowsForMissingUuid() {
		$json = $this->json_without( $this->new_metadata()->toJSON(), 'uuid' );
		$this->expectException( InvalidArgumentException::class );
		APNSRequestMetadata::fromJSON( $json );
	}

	public function testThatMetadataDeserializationIncludesPushTypeIfPresent() {
		$push_type = APNSPushType::MDM;
		$json = $this->json_adding( $this->new_metadata()->toJSON(), 'push_type', $push_type );
		$meta = APNSRequestMetadata::fromJSON( $json );
		$this->assertEquals( $push_type, $meta->getPushType() );
	}

	public function testThatMetadataDeserializationIncludesExpirationTimestampIfPresent() {
		$expiration_timestamp = time();
		$json = $this->json_adding( $this->new_metadata()->toJSON(), 'expiration_timestamp', $expiration_timestamp );
		$meta = APNSRequestMetadata::fromJSON( $json );
		$this->assertEquals( $expiration_timestamp, $meta->getExpirationTimestamp() );
	}

	public function testThatMetadataDeserializationIncludesLowPriorityIfPresent() {
		$priority = APNSPriority::THROTTLED;
		$json = $this->json_adding( $this->new_metadata()->toJSON(), 'priority', $priority );
		$meta = APNSRequestMetadata::fromJSON( $json );
		$this->assertEquals( $priority, $meta->getPriority() );
	}

	public function testThatMetadataDeserializationIncludesNormalPriorityIfPresent() {
		$priority = APNSPriority::IMMEDIATE;
		$json = $this->json_adding( $this->new_metadata()->toJSON(), 'priority', $priority );
		$meta = APNSRequestMetadata::fromJSON( $json );
		$this->assertEquals( $priority, $meta->getPriority() );
	}

	public function testThatMetadataDeserializationThrowsForInvalidPriority() {
		$json = $this->json_adding( $this->new_metadata()->toJSON(), 'priority', 0 );
		$this->expectException( InvalidArgumentException::class );
		APNSRequestMetadata::fromJSON( $json );
	}

	public function testThatMetadataDeserializationIncludesCollapseIdentifierIfPresent() {
		$collapse_identifier = $this->random_string();
		$json = $this->json_adding( $this->new_metadata()->toJSON(), 'collapse_identifier', $collapse_identifier );
		$meta = APNSRequestMetadata::fromJSON( $json );
		$this->assertEquals( $collapse_identifier, $meta->getCollapseIdentifier() );
	}
}
