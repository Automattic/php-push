<?php
declare( strict_types = 1 );

class APNSPriority {
	public const IMMEDIATE = 10;
	public const THROTTLED = 5;

	public static function is_valid( int $key ): bool {
		return in_array(
			$key,
			[
				self::IMMEDIATE,
				self::THROTTLED,
			],
			true
		);
	}
}
