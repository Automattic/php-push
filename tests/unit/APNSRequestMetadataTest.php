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
}
