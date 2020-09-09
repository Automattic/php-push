<?php
declare( strict_types = 1 );

// Keys defined at https://developer.apple.com/documentation/usernotifications/setting_up_a_remote_notification_server/generating_a_remote_notification#2943365
class APNSAlert implements JsonSerializable {

	/** @var string */
	protected $title;

	/** @var ?string */
	protected $body;

	/** @var ?string */
	protected $localized_title_key;

	/** @var ?array */
	protected $localized_title_args = null;

	/** @var ?string */
	protected $localized_action_key;

	/** @var ?string */
	protected $localized_message_key;

	/** @var ?array */
	protected $localized_message_args = null;

	/** @var ?string */
	protected $launch_image;

	public function __construct( string $title, ?string $body = null ) {
		$this->title = $title;
		$this->body  = $body;
	}

	public static function from_string( string $string ): APNSAlert {
		return new APNSAlert( $string );
	}

	public function get_title(): string {
		return $this->title;
	}

	public function set_title( string $title ): self {
		$this->title = $title;
		return $this;
	}

	public function get_body(): ?string {
		return $this->body;
	}

	public function set_body( string $body ): self {
		$this->body = $body;
		return $this;
	}

	public function get_localized_title_key(): ?string {
		return $this->localized_title_key;
	}

	public function set_localized_title_key( string $key ): self {
		$this->localized_title_key = $key;
		return $this;
	}

	public function get_localized_title_args(): ?array {
		return $this->localized_title_args;
	}

	public function set_localized_title_args( array $args ): self {
		$this->localized_title_args = $args;
		return $this;
	}

	public function get_localized_action_key(): ?string {
		return $this->localized_action_key;
	}

	public function set_localized_action_key( string $key ): self {
		$this->localized_action_key = $key;
		return $this;
	}

	public function get_localized_message_key(): ?string {
		return $this->localized_message_key;
	}

	public function set_localized_message_key( string $key ): self {
		$this->localized_message_key = $key;
		return $this;
	}

	public function get_localized_message_args(): ?array {
		return $this->localized_message_args;
	}

	public function set_localized_message_args( array $args ): self {
		$this->localized_message_args = $args;
		return $this;
	}

	public function get_launch_image(): ?string {
		return $this->launch_image;
	}

	public function set_launch_image( string $name ): self {
		$this->launch_image = $name;
		return $this;
	}

	/** @psalm-suppress InvalidReturnType */
	public function jsonSerialize() {

		$data = [
			'title'          => $this->title,
			'body'           => $this->body,
			'title-loc-key'  => $this->localized_title_key,
			'title-loc-args' => $this->localized_title_args,
			'action-loc-key' => $this->localized_action_key,
			'loc-key'        => $this->localized_message_key,
			'loc-args'       => $this->localized_message_args,
			'launch-image'   => $this->launch_image,
		];

		$output = array_filter(
			$data,
			function( $value ): bool {
				return ! is_null( $value );
			}
		);

		// If only the title is present, return it instead of an object
		if ( 1 === count( $output ) && 'title' === array_keys( $output )[0] ) {
			return $this->title;
		}

		return (object) $output;
	}
}
