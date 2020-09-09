<?php
declare( strict_types = 1 );

class APNSPushType {
	public const ALERT        = 'alert';
	public const BACKGROUND   = 'background';
	public const MDM          = 'mdm';
	public const VOIP         = 'voip';
	public const FILEPROVIDER = 'fileprovider';

	public static function is_valid( string $key ): bool {
		return in_array(
			$key,
			[
				self::ALERT,
				self::BACKGROUND,
				self::MDM,
				self::VOIP,
				self::FILEPROVIDER,
			],
			true
		);
	}
}
