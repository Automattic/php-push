<?php
declare( strict_types = 1 );
class APNSAlertTest extends APNSTest {
	public function testThatAlertConstructorStoresTitleAndBody() {
		$title = $this->random_string();
		$body = $this->random_string();

		$alert = new APNSAlert( $title, $body );
		$this->assertEquals( $title, $this->to_stdclass( $alert )->title );
		$this->assertEquals( $body, $this->to_stdclass( $alert )->body );
	}

	public function testThatAlertWithoutBodySerializesToString() {
		$string = $this->random_string();

		$alert = new APNSAlert( $string );
		$this->assertEquals( '"' . $string . '"', json_encode( $alert ) );
	}

	public function testThatAlertTitleSetterWorksForStrings() {
		$title = $this->random_string();
		$alert = $this->new_alert()->setTitle( $title );
		$this->assertEquals( $title, $this->to_stdclass( $alert )->title );
		$this->assertEquals( $title, $alert->getTitle() );
	}

	public function testThatAlertBodySetterWorks() {
		$body = $this->random_string();
		$alert = $this->new_alert()->setBody( $body );
		$this->assertEquals( $body, $this->to_stdclass( $alert )->body );
	}

	public function testThatLocalizedTitleKeyIsNotPresentByDefault() {
		$this->assertKeyIsNotPresentForObject( 'title-loc-key', $this->new_alert() );
	}

	public function testThatLocalizedTitleKeySetterWorks() {
		$key = $this->random_string();
		$alert = $this->new_alert()->setLocalizedTitleKey( $key );
		$this->assertEquals( $key, $this->to_stdclass( $alert )->{'title-loc-key'} );
	}

	public function testThatLocalizedTitleArgsSetterWorks() {
		$args = [ 'foo', 1, 1.25, '1.25', null ];
		$alert = $this->new_alert()->setLocalizedTitleArgs( $args );
		$this->assertEquals( $args, $this->to_stdclass( $alert )->{'title-loc-args'} );
	}

	public function testThatLocalizedActionKeySetterWorks() {
		$key = $this->random_string();
		$alert = $this->new_alert()->setLocalizedActionKey( $key );
		$this->assertEquals( $key, $this->to_stdclass( $alert )->{'action-loc-key'} );
	}

	public function testThatAlertLocalizedMessageKeySetterWorks() {
		$key = $this->random_string();
		$alert = $this->new_alert()->setLocalizedMessageKey( $key );
		$this->assertEquals( $key, $this->to_stdclass( $alert )->{'loc-key'} );
	}

	public function testThatAlertLocalizedMessageArgsSetterWorks() {
		$args = [ 'foo', 1, 1.25, '1.25', null ];
		$alert = $this->new_alert()->setLocalizedMessageArgs( $args );
		$this->assertEquals( $args, $this->to_stdclass( $alert )->{'loc-args'} );
	}

	public function testThatAlertLaunchImageSetterWorks() {
		$name = $this->random_string();
		$alert = $this->new_alert()->setLaunchImage( $name );
		$this->assertEquals( $name, $this->to_stdclass( $alert )->{'launch-image'} );
	}
}
