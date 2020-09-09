<?php
declare( strict_types = 1 );
// phpcs:disable WordPress.WP.AlternativeFunctions.json_encode_json_encode

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
	private function __construct() { }

	/**
	 * Create an APNSPayload from the provided string
	 *
	 * @param string $string
	 */
	public static function from_string( string $string ): APNSPayload {
		return self::from_alert( APNSAlert::from_string( $string ) );
	}

	/**
	 * Create an APNSPayload from the provided APNSAlert or string object
	 *
	 * @param APNSAlert $alert
	 */
	public static function from_alert( APNSAlert $alert ): APNSPayload {
		$payload = new APNSPayload();
		$payload->set_alert( $alert );
		return $payload;
	}

	/**
	 * Create an APNSPayload that contains only a badge count
	 *
	 * @param int $count
	 */
	public static function from_badge_count( int $count ): APNSPayload {
		return ( new APNSPayload() )
			->set_badge_count( $count );
	}

	public function get_alert(): ?APNSAlert {
		return $this->alert;
	}

	/**
	 * Set the alert field to the provided APNSAlert object or string.
	 *
	 * @param string|APNSAlert $alert
	 */
	public function set_alert( $alert ): self {

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

	public function get_badge_count(): ?int {
		return $this->badge;
	}

	public function set_badge_count( int $count ): self {
		$this->badge = $count;
		return $this;
	}

	/**
	 * Returns the currently set `APNSSound` object for this payload, or `null` if none exists.
	 *
	 * @return APNSSound|null
	 */
	public function get_sound(): ?APNSSound {
		return $this->sound;
	}

	/**
	 * Set the alert field to the provided APNSSound object or string.
	 *
	 * @param string|APNSSound $sound
	 */
	public function set_sound( $sound ): self {

		if ( is_string( $sound ) ) {
			$this->sound = APNSSound::from_string( $sound );
			return $this;
		}

		if ( is_object( $sound ) && get_class( $sound ) === APNSSound::class ) {
			$this->sound = $sound;
			return $this;
		}

		throw new InvalidArgumentException( 'Invalid Sound – you must pass either a string or `APNSSound` object.' );
	}

	public function get_is_content_available(): bool {
		return $this->content_available;
	}

	public function set_content_available( bool $content_available ): self {
		$this->content_available = $content_available;
		return $this;
	}

	public function get_is_mutable_content(): bool {
		return $this->mutable_content;
	}

	public function set_mutable_content( bool $mutable_content ): self {
		$this->mutable_content = $mutable_content;
		return $this;
	}

	public function get_target_content_id(): ?string {
		return $this->target_content_id;
	}

	public function set_target_content_id( string $id ): self {
		$this->target_content_id = $id;
		return $this;
	}

	public function get_category(): ?string {
		return $this->category;
	}

	public function set_category( string $category ): self {
		$this->category = $category;
		return $this;
	}

	public function get_thread_id(): ?string {
		return $this->thread_id;
	}

	public function set_thread_id( string $id ): self {
		$this->thread_id = $id;
		return $this;
	}

	public function get_custom_data(): array {
		return $this->custom;
	}

	public function set_custom_data( array $data ): self {
		$this->custom = $data;
		return $this;
	}

	public function to_json(): string {
		$payload_data = [
			'alert'             => $this->alert,
			'badge'             => $this->badge,
			'sound'             => $this->sound,
			'content-available' => $this->content_available ? 1 : null,
			'mutable-content'   => $this->mutable_content ? 1 : null,
			'target-content-id' => $this->target_content_id,
			'category'          => $this->category,
			'thread-id'         => $this->thread_id,
		];

		$payload_data = array_filter(
			$payload_data,
			function( $value ): bool {
				return ! is_null( $value );
			}
		);

		$all_data = array_merge( $this->custom, [ 'aps' => $payload_data ] );

		return json_encode( $all_data );
	}
}
