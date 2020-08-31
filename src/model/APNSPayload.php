<?php
declare( strict_types = 1 );

// Partial list of keys: https://developer.apple.com/documentation/usernotifications/setting_up_a_remote_notification_server/generating_a_remote_notification#2943363
class APNSPayload {

	/** @var ?APNSAlert */
	private $alert;

	/** @var ?int */
	private $badge = null;

	/** @var ?APNSSound */
	private $sound = null;

	/** @var bool */
	private $content_available = false;

	/** @var bool */
	private $mutable_content = false;

	/** @var ?string */
	private $target_content_id = null;

	/** @var ?string */
	private $category = null;

	/** @var ?string */
	private $thread_id = null;

	/** @var array */
	private $custom = [];

	// This class can be set up too many different ways to have any kind of shared constructor, so we'll explicitly define an empty one
	function __construct() { }

	/**
	 * Create an APNSPayload from the provided string
	 *
	 * @param string $string
	 */
	static function fromString( string $string ): APNSPayload {
		return APNSPayload::fromAlert( APNSAlert::fromString( $string ) );
	}

	/**
	 * Create an APNSPayload from the provided APNSAlert or string object
	 *
	 * @param APNSAlert $alert
	 */
	static function fromAlert( APNSAlert $alert ): APNSPayload {
		$payload = new APNSPayload();
		$payload->setAlert( $alert );
		return $payload;
	}

	}

	function getAlert(): ?APNSAlert {
		return $this->alert;
	}

	/**
	 * Set the alert field to the provided APNSAlert object or string.
	 *
	 * @param string|APNSAlert $alert
	 */
	function setAlert( $alert ): self {

		if ( is_string( $alert ) ) {
			$alert = new APNSAlert( $alert );
		}

		/** @psalm-suppress DocblockTypeContradiction – we need to validate that this is an object in case of external callers **/
		if ( ! is_object( $alert ) || get_class( $alert ) !== APNSAlert::class ) {
			throw new InvalidArgumentException( 'Invalid Alert – you must pass either a string or `APNSAlert` object.' );
		}

		$this->alert = $alert;
		return $this;
	}

	function getBadgeCount(): ?int {
		return $this->badge;
	}

	function setBadgeCount( int $count ): self {
		$this->badge = $count;
		return $this;
	}

	/**
	 * Returns the currently set `APNSSound` object for this payload, or `null` if none exists.
	 *
	 * @return APNSSound|null
	 */
	function getSound(): ?APNSSound {
		return $this->sound;
	}

	/**
	 * Set the alert field to the provided APNSSound object or string.
	 *
	 * @param string|APNSSound $sound
	 */
	function setSound( $sound ): self {

		if ( is_string( $sound ) ) {
			$this->sound = APNSSound::fromString( $sound );
			return $this;
		}

		if ( is_object( $sound ) && get_class( $sound ) === APNSSound::class ) {
			$this->sound = $sound;
			return $this;
		}

		throw new InvalidArgumentException( 'Invalid Sound – you must pass either a string or `APNSSound` object.' );
	}

	function getIsContentAvailable(): bool {
		return $this->content_available;
	}

	function setContentAvailable( bool $content_available ): self {
		$this->content_available = $content_available;
		return $this;
	}

	function getIsMutableContent(): bool {
		return $this->mutable_content;
	}

	function setMutableContent( bool $mutable_content ): self {
		$this->mutable_content = $mutable_content;
		return $this;
	}

	function getTargetContentId(): ?string {
		return $this->target_content_id;
	}

	function setTargetContentId( string $id ): self {
		$this->target_content_id = $id;
		return $this;
	}

	function getCategory(): ?string {
		return $this->category;
	}

	function setCategory( string $category ): self {
		$this->category = $category;
		return $this;
	}

	function getThreadId(): ?string {
		return $this->thread_id;
	}

	function setThreadId( string $id ): self {
		$this->thread_id = $id;
		return $this;
	}

	function getCustomData(): array {
		return $this->custom;
	}

	function setCustomData( array $data ): self {
		$this->custom = $data;
		return $this;
	}

	public function toJSON(): string {
		$payload_data = [
			'alert' => $this->alert,
			'badge' => $this->badge,
			'sound' => $this->sound,
			'content-available' => $this->content_available ? 1 : null,
			'mutable-content' => $this->mutable_content ? 1 : null,
			'target-content-id' => $this->target_content_id,
			'category' => $this->category,
			'thread-id' => $this->thread_id,
		];

		$payload_data = array_filter(
			$payload_data, function( $value ) {
				return ! is_null( $value );
			}
		);

		$all_data = array_merge( $this->custom, [ 'aps' => $payload_data ] );

		return json_encode( $all_data );
	}
}
