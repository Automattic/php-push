<?php
declare( strict_types = 1 );

class UUIDEncoder {
	public static function encodeInt( int $integer, string $padding ) {

		if ( $integer < 0 ) {
			throw new InvalidArgumentException( '$integer must be a positive integer' );
		}

		$hash = sha1( $padding );

		$segment_1 = substr( $hash, 0, 8 );
		$segment_2 = substr( $hash, 8, 4 );
		$segment_3 = substr( $hash, 12, 4 );
		$segment_4 = substr( $hash, 16, 4 );
		$segment_5 = str_pad( dechex( $integer ), 12, '0', STR_PAD_LEFT );

		return $segment_1 . '-' . $segment_2 . '-' . $segment_3 . '-' . $segment_4 . '-' . $segment_5;
	}

	public static function decodeInt( string $uuid ): int {
		$segments = explode( '-', $uuid );
		return hexdec( end( $segments ) );
	}
}
