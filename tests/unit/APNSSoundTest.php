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
		$this->assertEquals( $name, $this->encode( $sound )->name );
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
}
