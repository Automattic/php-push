<?php
declare( strict_types = 1 );
// phpcs:disable WordPress.WP.AlternativeFunctions.json_encode_json_encode

class APNSAlertTest extends APNSTest {
	public function testThatAlertConstructorStoresTitleAndBody() {
		$title = $this->random_string();
		$body  = $this->random_string();

		$alert = new APNSAlert( $title, $body );
		$this->assertEquals( $title, $this->to_stdclass( $alert )->title );
		$this->assertEquals( $body, $this->to_stdclass( $alert )->body );
	}

	public function testThatAlertWithoutBodySerializesToString() {
		$string = $this->random_string();

		$alert = new APNSAlert( $string );
		$this->assertEquals( $string, $alert->get_title() );
		$this->assertEquals( '"' . $string . '"', json_encode( $alert ) );
	}

	public function testThatAlertTitleSetterWorksForStrings() {
		$title = $this->random_string();
		$alert = $this->new_alert()->set_title( $title );
		$this->assertEquals( $title, $this->to_stdclass( $alert )->title );
		$this->assertEquals( $title, $alert->get_title() );
	}

	public function testThatAlertBodySetterWorks() {
		$body  = $this->random_string();
		$alert = $this->new_alert()->set_body( $body );
		$this->assertEquals( $body, $this->to_stdclass( $alert )->body );
		$this->assertEquals( $body, $alert->get_body() );
	}

	public function testThatLocalizedTitleKeyIsNotPresentByDefault() {
		$this->assertKeyIsNotPresentForObject( 'title-loc-key', $this->new_alert() );
	}

	public function testThatLocalizedTitleKeySetterWorks() {
		$key   = $this->random_string();
		$alert = $this->new_alert()->set_localized_title_key( $key );
		$this->assertEquals( $key, $this->to_stdclass( $alert )->{'title-loc-key'} );
		$this->assertEquals( $key, $alert->get_localized_title_key() );
	}

	public function testThatLocalizedTitleArgsSetterWorks() {
		$args  = [ 'foo', 1, 1.25, '1.25', null ];
		$alert = $this->new_alert()->set_localized_title_args( $args );
		$this->assertEquals( $args, $this->to_stdclass( $alert )->{'title-loc-args'} );
		$this->assertEquals( $args, $alert->get_localized_title_args() );
	}

	public function testThatLocalizedActionKeySetterWorks() {
		$key   = $this->random_string();
		$alert = $this->new_alert()->set_localized_action_key( $key );
		$this->assertEquals( $key, $this->to_stdclass( $alert )->{'action-loc-key'} );
		$this->assertEquals( $key, $alert->get_localized_action_key() );
	}

	public function testThatAlertLocalizedMessageKeySetterWorks() {
		$key   = $this->random_string();
		$alert = $this->new_alert()->set_localized_message_key( $key );
		$this->assertEquals( $key, $this->to_stdclass( $alert )->{'loc-key'} );
		$this->assertEquals( $key, $alert->get_localized_message_key() );
	}

	public function testThatAlertLocalizedMessageArgsSetterWorks() {
		$args  = [ 'foo', 1, 1.25, '1.25', null ];
		$alert = $this->new_alert()->set_localized_message_args( $args );
		$this->assertEquals( $args, $this->to_stdclass( $alert )->{'loc-args'} );
		$this->assertEquals( $args, $alert->get_localized_message_args() );
	}

	public function testThatAlertLaunchImageSetterWorks() {
		$name  = $this->random_string();
		$alert = $this->new_alert()->set_launch_image( $name );
		$this->assertEquals( $name, $this->to_stdclass( $alert )->{'launch-image'} );
		$this->assertEquals( $name, $alert->get_launch_image() );
	}
}
