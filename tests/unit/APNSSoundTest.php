<?php
declare( strict_types = 1 );
// phpcs:disable WordPress.WP.AlternativeFunctions.json_encode_json_encode

class APNSSoundTest extends APNSTest {

	public function testThatInitializationStoresName() {
		$name  = $this->random_string();
		$sound = new APNSSound( $name );
		$this->assertEquals( $name, $sound->get_name() );
	}

	public function testThatNameSetterWorks() {
		$name  = $this->random_string();
		$sound = $this->new_sound()->set_name( $name );
		$this->assertEquals( $name, $this->to_string( $sound ) );
		$this->assertEquals( $name, $sound->get_name() );
	}

	public function testThatDefaultVolumeIsMax() {
		$this->assertEquals( 1.0, $this->new_sound()->get_volume() );
	}

	public function testThatVolumeSetterWorks() {
		$volume = 0.5;
		$sound  = $this->new_sound()->set_volume( $volume );
		$this->assertEquals( $volume, $sound->get_volume() );
	}

	public function testThatVolumeSetterThrowsForNegativeValues() {
		$volume = -0.1;
		$this->expectException( InvalidArgumentException::class );
		$this->new_sound()->set_volume( $volume );
	}

	public function testThatVolumeSetterThrowsForValuesGreaterThanOne() {
		$volume = 1.1;
		$this->expectException( InvalidArgumentException::class );
		$this->new_sound()->set_volume( $volume );
	}

	public function testThatDefaultIsCriticalValueIsFalse() {
		$this->assertFalse( $this->new_sound()->get_is_critical() );
	}

	public function testThatIsCriticalSetterWorks() {
		$sound = $this->new_sound()->set_is_critical( true );
		$this->assertTrue( $sound->get_is_critical() );

		$sound = $this->new_sound()->set_is_critical( false );
		$this->assertFalse( $sound->get_is_critical() );
	}

	public function testThatSerializationForDefaultsReturnsString() {
		$name  = $this->random_string();
		$sound = APNSSound::from_string( $name );
		$this->assertEquals( '"' . $name . '"', json_encode( $sound ) );
	}

	public function testThatSerializationForCustomVolumeReturnsObject() {
		$volume = random_int( 0, 9 ) / 10;
		$sound  = $this->new_sound()->set_volume( $volume );
		$this->assertEquals( $volume, $this->to_stdclass( $sound )->volume );
	}

	public function testThatSerializationForCustomCriticalityReturnsObject() {
		$sound = $this->new_sound()->set_is_critical( true );
		$this->assertEquals( 1, $this->to_stdclass( $sound )->critical );
	}
}
