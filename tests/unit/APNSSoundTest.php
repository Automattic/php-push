<?php
declare( strict_types = 1 );
class APNSSoundTest extends APNSTest {

	public function testThatInitializationStoresName() {
		$name = $this->random_string();
		$sound = new APNSSound( $name );
		$this->assertEquals( $name, $sound->getName() );
	}

	public function testThatNameSetterWorks() {
		$name = $this->random_string();
		$sound = $this->new_sound()->setName( $name );
		$this->assertEquals( $name, $this->to_string( $sound ) );
		$this->assertEquals( $name, $sound->getName() );
	}

	public function testThatDefaultVolumeIsMax() {
		$this->assertEquals( 1.0, $this->new_sound()->getVolume() );
	}

	public function testThatVolumeSetterWorks() {
		$volume = 0.5;
		$sound = $this->new_sound()->setVolume( $volume );
		$this->assertEquals( $volume, $sound->getVolume() );
	}

	public function testThatVolumeSetterThrowsForNegativeValues() {
		$volume = -0.1;
		$this->expectException( InvalidArgumentException::class );
		$this->new_sound()->setVolume( $volume );
	}

	public function testThatVolumeSetterThrowsForValuesGreaterThanOne() {
		$volume = 1.1;
		$this->expectException( InvalidArgumentException::class );
		$this->new_sound()->setVolume( $volume );
	}

	public function testThatDefaultIsCriticalValueIsFalse() {
		$this->assertFalse( $this->new_sound()->getIsCritical() );
	}

	public function testThatIsCriticalSetterWorks() {
		$sound = $this->new_sound()->setIsCritical( true );
		$this->assertTrue( $sound->getIsCritical() );

		$sound = $this->new_sound()->setIsCritical( false );
		$this->assertFalse( $sound->getIsCritical() );
	}

	public function testThatSerializationForDefaultsReturnsString() {
		$name = $this->random_string();
		$sound = APNSSound::fromString( $name );
		$this->assertEquals( '"' . $name . '"', json_encode( $sound ) );
	}

	public function testThatSerializationForCustomVolumeReturnsObject() {
		$volume = rand( 0, 10 ) / 10;
		$sound = $this->new_sound()->setVolume( $volume );
		$this->assertEquals( $volume, $this->to_stdclass( $sound )->volume );
	}

	public function testThatSerializationForCustomCriticalityReturnsObject() {
		$sound = $this->new_sound()->setIsCritical( true );
		$this->assertEquals( 1, $this->to_stdclass( $sound )->critical );
	}
}
