<?php
declare( strict_types = 1 );

class APNSPushType {
	public const ALERT = 'alert';
	public const BACKGROUND = 'background';
	public const MDM = 'mdm';
	public const VOIP = 'voip';
	public const FILEPROVIDER = 'fileprovider';

	public static function isValid( string $key ): bool {
		return in_array(
			$key, [
				APNSPushType::ALERT,
				APNSPushType::BACKGROUND,
				APNSPushType::MDM,
				APNSPushType::VOIP,
				APNSPushType::FILEPROVIDER,
			], true
		);
	}
}
