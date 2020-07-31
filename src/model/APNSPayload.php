<?php
declare( strict_types = 1 );

// Partial list of keys: https://developer.apple.com/documentation/usernotifications/setting_up_a_remote_notification_server/generating_a_remote_notification#2943363
class APNSPayload {

	/** @var string|APNSAlert */
	private $alert = '';

	/** @var ?int */
	private $badge = null;

	/** @var ?string|?APNSSound */
	private $sound = null;

	/** @var ?bool */
	private $content_available = null;

	/** @var ?bool */
	private $mutable_content = null;

	/** @var ?string */
	private $target_content_id = null;

	/** @var ?string */
	private $category = null;

	/** @var ?string */
	private $thread_id = null;

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
			$this->alert = $alert;
			return $this;
		}

		if ( is_object( $alert ) && get_class( $alert ) === APNSAlert::class ) {
			$this->alert = $alert;
			return $this;
		}

		throw new InvalidArgumentException( 'Invalid Alert – you must pass either a string or `APNSAlert` object.' );
	}

	function setBadgeCount( int $count ): self {
		$this->badge = $count;
		return $this;
	}

	/**
	 * Set the alert field to the provided APNSSound object or string.
	 *
	 * @param string|APNSSound $sound
	 */
	function setSound( $sound ): self {

		if ( is_string( $sound ) ) {
			$this->sound = $sound;
			return $this;
		}

		if ( is_object( $sound ) && get_class( $sound ) === APNSSound::class ) {
			$this->sound = $sound;
			return $this;
		}

		throw new InvalidArgumentException( 'Invalid Sound – you must pass either a string or `APNSSound` object.' );
	}

	function setContentAvailable( bool $content_available ): self {
		$this->content_available = $content_available;
		return $this;
	}

	function setMutableContent( bool $mutable_content ): self {
		$this->mutable_content = $mutable_content;
		return $this;
	}

	function setTargetContentId( string $id ): self {
		$this->target_content_id = $id;
		return $this;
	}

	function setCategory( string $category ): self {
		$this->category = $category;
		return $this;
	}

	function setThreadId( string $id ): self {
		$this->thread_id = $id;
		return $this;
	}

	function setCustomData( array $data ): self {
		$this->custom = $data;
		return $this;
	}

	public function toJSON(): string {
		return json_encode(
			array_merge(
				$this->custom,
				[
					'aps' => (object) array_filter(
						[
							'alert' => $this->alert,
							'badge' => $this->badge,
							'sound' => $this->sound,
							'content-available' => $this->content_available,
							'mutable-content' => $this->mutable_content,
							'target-content-id' => $this->target_content_id,
							'category' => $this->category,
							'thread-id' => $this->thread_id,
						], function( $value ) { return ! is_null( $value ); }
					),
				]
			)
		);
	}
}
