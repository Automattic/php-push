<?php
declare( strict_types = 1 );

// Partial list of keys: https://developer.apple.com/documentation/usernotifications/setting_up_a_remote_notification_server/generating_a_remote_notification#2943363
class APNSPayload implements JsonSerializable {

	private $custom = [];

	function __construct( APNSAlert $alert ) {
		$this->internal['alert'] = $alert;
	}

	function setAlert( APNSAlert $alert ) {
		$this->internal['alert'] = $alert;
		return $this;
	}

	function setBadgeCount( int $count ) {
		$this->internal['badge'] = $count;
		return $this;
	}

	function setSound( $sound ) {

		if ( is_string( $sound ) ) {
			$this->internal['sound'] = $sound;
			return $this;
		}

		if ( is_object( $sound ) && get_class( $sound ) === APNSSound::class ) {
			$this->internal['sound'] = $sound;
			return $this;
		}

		throw new InvalidArgumentException( 'Invalid Sound – you must pass either a string or `APNSSound` object.' );
	}

	function setContentAvailable( bool $content_available ) {
		$this->internal['content-available'] = $content_available ? 1 : 0;
		return $this;
	}

	function setMutableContent( bool $mutable ) {
		$this->internal['mutable-content'] = $mutable ? 1 : 0;
		return $this;
	}

	function setTargetContentId( string $id ) {
		$this->internal['target-content-id'] = $id;
		return $this;
	}

	function setCategory( string $category ) {
		$this->internal['category'] = $category;
		return $this;
	}

	function setThreadId( string $id ) {
		$this->internal['thread-id'] = $id;
		return $this;
	}

	function setCustomData( array $data ) {
		$this->custom = $data;
		return $this;
	}

	public function jsonSerialize() {
		return array_merge(
			$this->custom,
			[
				'aps' => $this->internal,
			]
		);
	}
}
