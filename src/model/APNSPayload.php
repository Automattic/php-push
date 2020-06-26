<?php
declare( strict_types = 1 );

// Partial list of keys: https://developer.apple.com/documentation/usernotifications/setting_up_a_remote_notification_server/generating_a_remote_notification#2943363
class APNSPayload implements JsonSerializable {

	/** @var array */
	private $internal = [];

	/** @var array */
	private $custom = [];

	/**
	 * Create an APNSPayload from the provided APNSAlert or string object, as well as any custom data
	 *
	 * @param string|APNSAlert $alert
	 * @param array $custom
	 */
	function __construct( $alert, array $custom = [] ) {
		$this->setAlert( $alert );
		$this->custom = $custom;
	}

	/**
	 * Set the alert field to the provided APNSAlert object or string.
	 *
	 * @param string|APNSAlert $alert
	 */
	function setAlert( $alert ): self {

		if ( is_string( $alert ) ) {
			$this->internal['alert'] = $alert;
			return $this;
		}

		if ( is_object( $alert ) && get_class( $alert ) === APNSAlert::class ) {
			$this->internal['alert'] = $alert;
			return $this;
		}

		throw new InvalidArgumentException( 'Invalid Alert – you must pass either a string or `APNSAlert` object.' );
	}

	function setBadgeCount( int $count ): self {
		$this->internal['badge'] = $count;
		return $this;
	}

	/**
	 * Set the alert field to the provided APNSSound object or string.
	 *
	 * @param string|APNSSound $sound
	 */
	function setSound( $sound ): self {

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

	function setContentAvailable( bool $content_available ): self {
		$this->internal['content-available'] = $content_available ? 1 : 0;
		return $this;
	}

	function setMutableContent( bool $mutable ): self {
		$this->internal['mutable-content'] = $mutable ? 1 : 0;
		return $this;
	}

	function setTargetContentId( string $id ): self {
		$this->internal['target-content-id'] = $id;
		return $this;
	}

	function setCategory( string $category ): self {
		$this->internal['category'] = $category;
		return $this;
	}

	function setThreadId( string $id ): self {
		$this->internal['thread-id'] = $id;
		return $this;
	}

	function setCustomData( array $data ): self {
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
