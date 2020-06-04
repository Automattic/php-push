<?php
declare( strict_types = 1 );
class APNSAlertTest extends APNSTest {
	public function testThatAlertConstructorStoresTitleAndBody() {
		$title = 'title';
		$body = 'body';

		$alert = new APNSAlert( $title, $body );
		$this->assertEquals( $title, $this->encode( $alert )->title );
		$this->assertEquals( $body, $this->encode( $alert )->body );
	}

	public function testThatAlertTitleSetterWorks() {
		$title = 'title';
		$alert = $this->new_alert()->setTitle( $title );
		$this->assertEquals( $title, $this->encode( $alert )->title );
	}

	public function testThatAlertBodySetterWorks() {
		$body = 'body';
		$alert = $this->new_alert()->setBody( $body );
		$this->assertEquals( $body, $this->encode( $alert )->body );
	}

	public function testThatLocalizedTitleKeyIsNotPresentByDefault() {
		$this->assertKeyIsNotPresentForObject( 'title-loc-key', $this->new_alert() );
	}

	public function testThatLocalizedTitleKeySetterWorks() {
		$key = 'key';
		$alert = $this->new_alert()->setLocalizedTitleKey( $key );
		$this->assertEquals( $key, $this->encode( $alert )->{'title-loc-key'} );
	}

	public function testThatLocalizedTitleArgsSetterWorks() {
		$args = [ 'foo', 1, 1.25, '1.25', null ];
		$alert = $this->new_alert()->setLocalizedTitleArgs( $args );
		$this->assertEquals( $args, $this->encode( $alert )->{'title-loc-args'} );
	}

	public function testThatLocalizedActionKeySetterWorks() {
		$key = 'key';
		$alert = $this->new_alert()->setLocalizedActionKey( $key );
		$this->assertEquals( $key, $this->encode( $alert )->{'action-loc-key'} );
	}

	public function testThatAlertLocalizedMessageKeySetterWorks() {
		$key = 'key';
		$alert = $this->new_alert()->setLocalizedMessageKey( $key );
		$this->assertEquals( $key, $this->encode( $alert )->{'loc-key'} );
	}

	public function testThatAlertLocalizedMessageArgsSetterWorks() {
		$args = [ 'foo', 1, 1.25, '1.25', null ];
		$alert = $this->new_alert()->setLocalizedMessageArgs( $args );
		$this->assertEquals( $args, $this->encode( $alert )->{'loc-args'} );
	}

	public function testThatAlertLaunchImageSetterWorks() {
		$name = 'file-name';
		$alert = $this->new_alert()->setLaunchImage( $name );
		$this->assertEquals( $name, $this->encode( $alert )->{'launch-image'} );
	}
}
