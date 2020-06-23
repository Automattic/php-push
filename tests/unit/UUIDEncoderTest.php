<?php
declare( strict_types = 1 );

class UUIDEncoderTest extends APNSTest {

	function testThatEncodingAnDecodingWorksCorrectly() {
		$number = random_int( 1, PHP_INT_MAX );

		$encoded = UUIDEncoder::encodeInt( $number, '' );
		$decoded = UUIDEncoder::decodeInt( $encoded );

		$this->assertEquals( $number, $decoded );
	}

	function testThatEncodingZeroWorksCorrectly() {
		$number = 0;

		$encoded = UUIDEncoder::encodeInt( $number, '' );
		$decoded = UUIDEncoder::decodeInt( $encoded );

		$this->assertEquals( $number, $decoded );
	}

	function testThatEncodingPHPIntMaxWorksCorrectly() {
		$number = PHP_INT_MAX;

		$encoded = UUIDEncoder::encodeInt( $number, '' );
		$decoded = UUIDEncoder::decodeInt( $encoded );

		$this->assertEquals( $number, $decoded );
	}

	function testThatParserThrowsForNegativeNumbers() {
		$this->expectException( InvalidArgumentException::class );
		UUIDEncoder::encodeInt( -1, '' );
	}
}
