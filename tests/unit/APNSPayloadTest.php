<?php
declare( strict_types = 1 );
class APNSPayloadTest extends APNSTest {

	public function testThatAlertSetterWorksForAlertObjects() {
		$alert = $this->new_alert();
		$payload = $this->new_payload()->setAlert( $alert );
		$this->assertEquals( $this->to_stdclass( $alert ), $this->to_stdclass( $payload )->aps->alert );
	}

	public function testThatAlertSetterWorksForStrings() {
		$alert = $this->random_string();
		$payload = $this->new_payload()->setAlert( $alert );
		$this->assertEquals( $alert, $this->to_stdclass( $payload )->aps->alert );
	}

	public function testThatAlertSetterThrowsForInvalidValues() {
		$this->expectException( InvalidArgumentException::class );
		$this->new_payload()->setAlert( 0 );
	}

	public function testThatSoundIsNotPresentByDefault() {
		$object = $this->to_stdclass( $this->new_payload() );
		$this->assertKeyIsNotPresentForObject( 'sound', $object->aps );
	}

	public function testThatSoundSetterWorksForStrings() {
		$sound = $this->random_string();
		$payload = $this->new_payload()->setSound( $sound );
		$this->assertEquals( $sound, $this->to_stdclass( $payload )->aps->sound );
	}

	public function testThatSoundSetterWorksForSoundObjects() {
		$sound = $this->new_sound();
		$payload = $this->new_payload()->setSound( $sound );
		$this->assertEquals( $sound->getName(), $this->to_stdclass( $payload )->aps->sound );
	}

	public function testThatSoundSetterThrowsForInvalidValues() {
		$this->expectException( InvalidArgumentException::class );
		$this->new_payload()->setSound( 0 );
	}

	public function testThatBadgeCountIsNotPresentByDefault() {
		$object = $this->to_stdclass( $this->new_payload() );
		$this->assertKeyIsNotPresentForObject( 'badge', $object->aps );
	}

	public function testThatBadgeCountSetterWorks() {
		$badge_count = random_int( 1, 99 );
		$payload = $this->new_payload()->setBadgeCount( $badge_count );
		$this->assertEquals( $badge_count, $this->to_stdclass( $payload )->aps->badge );
	}

	public function testThatContentAvailableIsNotPresentByDefault() {
		$this->assertKeyIsNotPresentForObject( 'content-available', $this->new_payload() );
	}

	public function testThatSetContentAvailableWorks() {
		$payload = $this->new_payload()->setContentAvailable( true );
		$this->assertEquals( 1, $this->to_stdclass( $payload )->aps->{'content-available'} );

		$payload = $payload->setContentAvailable( false );
		$this->assertEquals( 0, $this->to_stdclass( $payload )->aps->{'content-available'} );
	}

	public function testThatMutableContentIsNotPresentByDefault() {
		$this->assertKeyIsNotPresentForObject( 'mutable-content', $this->new_payload() );
	}

	public function testThatMutableContentSetterWorks() {
		$payload = $this->new_payload()->setMutableContent( true );
		$this->assertEquals( 1, $this->to_stdclass( $payload )->aps->{'mutable-content'} );

		$payload = $payload->setMutableContent( false );
		$this->assertEquals( 0, $this->to_stdclass( $payload )->aps->{'mutable-content'} );
	}

	public function testThatTargetContentIdIsNotPresentByDefault() {
		$this->assertKeyIsNotPresentForObject( 'target-content-id', $this->new_payload() );
	}

	public function testThatTargetContentIdSetterWorks() {
		$id = $this->random_string();
		$payload = $this->new_payload()->setTargetContentId( $id );
		$this->assertEquals( $id, $this->to_stdclass( $payload )->aps->{'target-content-id'} );
	}

	public function testThatCategoryIsNotPresentByDefault() {
		$this->assertKeyIsNotPresentForObject( 'category', $this->new_payload() );
	}

	public function testThatCategorySetterWorks() {
		$category = $this->random_string();
		$payload = $this->new_payload()->setCategory( $category );
		$this->assertEquals( $category, $this->to_stdclass( $payload )->aps->category );
	}

	public function testThatThreadIdIsNotPresentByDefault() {
		$this->assertKeyIsNotPresentForObject( 'thread-id', $this->new_payload() );
	}

	public function testThatThreadIdSetterWorks() {
		$thread = $this->random_string();
		$payload = $this->new_payload()->setThreadId( $thread );
		$this->assertEquals( $thread, $this->to_stdclass( $payload )->aps->{'thread-id'} );
	}

	// The only key that should be present by default is `aps`
	public function testThatNoCustomDataIsPresentByDefault() {
		$payload = $this->to_stdclass( $this->new_payload() );
		$this->assertEquals( 1, count( get_object_vars( $payload ) ) );
	}

	public function testThatCustomDataSetterWorks() {
		$custom_data = [
			'foo' => 'bar',
			'baz' => [ 1.0, 1, '1', 0x1 ],
		];
		$payload = $this->new_payload()->setCustomData( $custom_data );
		$object = $this->to_stdclass( $payload );
		unset( $object->aps );
		$this->assertEquals( $custom_data, (array) $object );
	}
}
