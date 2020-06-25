<?php
declare( strict_types = 1 );
class APNSPayloadTest extends APNSTest {

	public function testThatAlertSetterWorksForAlertObjects() {
		$alert = $this->new_alert();
		$push = $this->new_payload()->setAlert( $alert );
		$this->assertEquals( $this->encode( $alert ), $this->encode( $push )->aps->alert );
	}

	public function testThatAlertSetterWorksForStrings() {
		$alert = $this->random_string();
		$push = $this->new_payload()->setAlert( $alert );
		$this->assertEquals( $alert, $this->encode( $push )->aps->alert );
	}

	public function testThatAlertSetterThrowsForInvalidValues() {
		$this->expectException( InvalidArgumentException::class );
		$this->new_payload()->setAlert( 0 );
	}

	public function testThatSoundIsNotPresentByDefault() {
		$object = $this->encode( $this->new_payload() );
		$this->assertKeyIsNotPresentForObject( 'sound', $object->aps );
	}

	public function testThatSoundSetterWorksForStrings() {
		$sound = $this->random_string();
		$push = $this->new_payload()->setSound( $sound );
		$this->assertEquals( $sound, $this->encode( $push )->aps->sound );
	}

	public function testThatSoundSetterWorksForSoundObjects() {
		$sound = $this->new_sound();
		$push = $this->new_payload()->setSound( $sound );
		$this->assertEquals( $this->encode( $sound ), $this->encode( $push )->aps->sound );
	}

	public function testThatSoundSetterThrowsForInvalidValues() {
		$this->expectException( InvalidArgumentException::class );
		$this->new_payload()->setSound( 0 );
	}

	public function testThatBadgeCountIsNotPresentByDefault() {
		$object = $this->encode( $this->new_payload() );
		$this->assertKeyIsNotPresentForObject( 'badge', $object->aps );
	}

	public function testThatBadgeCountSetterWorks() {
		$badge_count = random_int( 1, 99 );
		$push = $this->new_payload()->setBadgeCount( $badge_count );
		$this->assertEquals( $badge_count, $this->encode( $push )->aps->badge );
	}

	public function testThatContentAvailableIsNotPresentByDefault() {
		$this->assertKeyIsNotPresentForObject( 'content-available', $this->new_payload() );
	}

	public function testThatSetContentAvailableWorks() {
		$push = $this->new_payload()->setContentAvailable( true );
		$this->assertEquals( 1, $this->encode( $push )->aps->{'content-available'} );

		$push = $push->setContentAvailable( false );
		$this->assertEquals( 0, $this->encode( $push )->aps->{'content-available'} );
	}

	public function testThatMutableContentIsNotPresentByDefault() {
		$this->assertKeyIsNotPresentForObject( 'mutable-content', $this->new_payload() );
	}

	public function testThatMutableContentSetterWorks() {
		$push = $this->new_payload()->setMutableContent( true );
		$this->assertEquals( 1, $this->encode( $push )->aps->{'mutable-content'} );

		$push = $push->setMutableContent( false );
		$this->assertEquals( 0, $this->encode( $push )->aps->{'mutable-content'} );
	}

	public function testThatTargetContentIdIsNotPresentByDefault() {
		$this->assertKeyIsNotPresentForObject( 'target-content-id', $this->new_payload() );
	}

	public function testThatTargetContentIdSetterWorks() {
		$id = $this->random_string();
		$push = $this->new_payload()->setTargetContentId( $id );
		$this->assertEquals( $id, $this->encode( $push )->aps->{'target-content-id'} );
	}

	public function testThatCategoryIsNotPresentByDefault() {
		$this->assertKeyIsNotPresentForObject( 'category', $this->new_payload() );
	}

	public function testThatCategorySetterWorks() {
		$category = $this->random_string();
		$push = $this->new_payload()->setCategory( $category );
		$this->assertEquals( $category, $this->encode( $push )->aps->category );
	}

	public function testThatThreadIdIsNotPresentByDefault() {
		$this->assertKeyIsNotPresentForObject( 'thread-id', $this->new_payload() );
	}

	public function testThatThreadIdSetterWorks() {
		$thread = $this->random_string();
		$push = $this->new_payload()->setThreadId( $thread );
		$this->assertEquals( $thread, $this->encode( $push )->aps->{'thread-id'} );
	}

	// The only key that should be present by default is `aps`
	public function testThatNoCustomDataIsPresentByDefault() {
		$push = $this->encode( $this->new_payload() );
		$this->assertEquals( 1, count( get_object_vars( $push ) ) );
		$this->assertEquals( 'aps', array_keys( get_object_vars( $push ) )[0] );
	}

	public function testThatCustomDataSetterWorks() {
		$custom_data = [
			'foo' => 'bar',
			'baz' => [ 1.0, 1, '1', 0x1 ],
		];
		$push = $this->new_payload()->setCustomData( $custom_data );
		$object = $this->encode_to_array( $push );
		unset( $object['aps'] );
		$this->assertEquals( $custom_data, $object );
	}
}
