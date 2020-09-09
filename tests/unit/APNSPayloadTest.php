<?php
declare( strict_types = 1 );
class APNSPayloadTest extends APNSTest {

	public function testThatBadgeCountInitializerOnlySetsBadgeCount() {
		$count   = random_int( 0, PHP_INT_MAX );
		$payload = APNSPayload::from_badge_count( $count );
		$this->assertEquals( $count, $this->to_stdclass( $payload )->aps->badge );
		$this->assertEquals( 1, count( get_object_vars( $this->to_stdclass( $payload )->aps ) ) );
	}

	public function testThatAlertInitializerSetsAlertCorrectly() {
		$alert   = $this->new_alert();
		$payload = APNSPayload::from_alert( $alert );
		$this->assertEquals( $this->to_stdclass( $alert ), $this->to_stdclass( $payload )->aps->alert );
	}

	public function testThatAlertSetterWorksForAlertObjects() {
		$alert   = $this->new_alert();
		$payload = $this->new_payload()->set_alert( $alert );
		$this->assertEquals( $this->to_stdclass( $alert ), $this->to_stdclass( $payload )->aps->alert );
	}

	public function testThatAlertSetterWorksForStrings() {
		$alert   = $this->random_string();
		$payload = $this->new_payload()->set_alert( $alert );
		$this->assertEquals( $alert, $this->to_stdclass( $payload )->aps->alert );
		$this->assertEquals( $alert, $payload->get_alert()->get_title() );
	}

	public function testThatAlertSetterThrowsForInvalidValues() {
		$this->expectException( InvalidArgumentException::class );
		$this->new_payload()->set_alert( 0 );
	}

	public function testThatSoundIsNotPresentByDefault() {
		$object = $this->to_stdclass( $this->new_payload() );
		$this->assertKeyIsNotPresentForObject( 'sound', $object->aps );
	}

	public function testThatSoundSetterWorksForStrings() {
		$sound   = $this->random_string();
		$payload = $this->new_payload()->set_sound( $sound );
		$this->assertEquals( $sound, $this->to_stdclass( $payload )->aps->sound );
		$this->assertEquals( $sound, $payload->get_sound()->get_name() );
	}

	public function testThatSoundSetterWorksForSoundObjects() {
		$sound   = $this->new_sound();
		$payload = $this->new_payload()->set_sound( $sound );
		$this->assertEquals( $sound->get_name(), $this->to_stdclass( $payload )->aps->sound );
		$this->assertEquals( $sound, $payload->get_sound() );
	}

	public function testThatSoundSetterThrowsForInvalidValues() {
		$this->expectException( InvalidArgumentException::class );
		$this->new_payload()->set_sound( 0 );
	}

	public function testThatBadgeCountIsNotPresentByDefault() {
		$object = $this->to_stdclass( $this->new_payload() );
		$this->assertKeyIsNotPresentForObject( 'badge', $object->aps );
	}

	public function testThatBadgeCountSetterWorks() {
		$badge_count = random_int( 1, 99 );
		$payload     = $this->new_payload()->set_badge_count( $badge_count );
		$this->assertEquals( $badge_count, $this->to_stdclass( $payload )->aps->badge );
		$this->assertEquals( $badge_count, $payload->get_badge_count() );
	}

	public function testThatContentAvailableIsNotPresentByDefault() {
		$this->assertKeyIsNotPresentForObject( 'content-available', $this->to_stdclass( $this->new_payload() )->aps );
	}

	public function testThatSetContentAvailableWorks() {
		$payload = $this->new_payload()->set_content_available( true );
		$this->assertSame( 1, $this->to_stdclass( $payload )->aps->{'content-available'} );
		$this->assertTrue( $payload->get_is_content_available() );

		$payload = $payload->set_content_available( false );
		$this->assertKeyIsNotPresentForObject( 'content-available', $this->to_stdclass( $payload )->aps );
		$this->assertFalse( $payload->get_is_content_available() );
	}

	public function testThatMutableContentIsNotPresentByDefault() {
		$this->assertKeyIsNotPresentForObject( 'mutable-content', $this->to_stdclass( $this->new_payload() )->aps );
	}

	public function testThatMutableContentSetterWorks() {
		$payload = $this->new_payload()->set_mutable_content( true );
		$this->assertSame( 1, $this->to_stdclass( $payload )->aps->{'mutable-content'} );
		$this->assertTrue( $payload->get_is_mutable_content() );

		$payload = $payload->set_mutable_content( false );
		$this->assertKeyIsNotPresentForObject( 'mutable-content', $payload );
		$this->assertFalse( $payload->get_is_mutable_content() );
	}

	public function testThatTargetContentIdIsNotPresentByDefault() {
		$this->assertKeyIsNotPresentForObject( 'target-content-id', $this->new_payload() );
	}

	public function testThatTargetContentIdSetterWorks() {
		$id      = $this->random_string();
		$payload = $this->new_payload()->set_target_content_id( $id );
		$this->assertEquals( $id, $this->to_stdclass( $payload )->aps->{'target-content-id'} );
		$this->assertEquals( $id, $payload->get_target_content_id() );
	}

	public function testThatCategoryIsNotPresentByDefault() {
		$this->assertKeyIsNotPresentForObject( 'category', $this->new_payload() );
	}

	public function testThatCategorySetterWorks() {
		$category = $this->random_string();
		$payload  = $this->new_payload()->set_category( $category );
		$this->assertEquals( $category, $this->to_stdclass( $payload )->aps->category );
		$this->assertEquals( $category, $payload->get_category() );
	}

	public function testThatThreadIdIsNotPresentByDefault() {
		$this->assertKeyIsNotPresentForObject( 'thread-id', $this->new_payload() );
	}

	public function testThatThreadIdSetterWorks() {
		$thread  = $this->random_string();
		$payload = $this->new_payload()->set_thread_id( $thread );
		$this->assertEquals( $thread, $this->to_stdclass( $payload )->aps->{'thread-id'} );
		$this->assertEquals( $thread, $payload->get_thread_id() );
	}

	// The only key that should be present by default is `aps`
	public function testThatNoCustomDataIsPresentByDefault() {
		$payload = $this->to_stdclass( $this->new_payload() );
		$this->assertEquals( 1, count( get_object_vars( $payload ) ) );
		$this->assertEquals( 'aps', array_keys( get_object_vars( $payload ) )[0] );
	}

	public function testThatCustomDataSetterWorks() {
		$custom_data = [
			'foo' => 'bar',
			'baz' => [ 1.0, 1, '1', 0x1 ],
		];
		$payload     = $this->new_payload()->set_custom_data( $custom_data );
		$object      = $this->to_stdclass( $payload );
		unset( $object->aps );
		$this->assertEquals( $custom_data, (array) $object );
		$this->assertEquals( $custom_data, $payload->get_custom_data() );
	}
}
