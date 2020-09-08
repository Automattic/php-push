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
		$this->body = $body;
	}

	public static function fromString( string $string ): APNSAlert {
		return new APNSAlert( $string );
	}

	public function getTitle(): string {
		return $this->title;
	}

	public function setTitle( string $title ): self {
		$this->title = $title;
		return $this;
	}

	public function getBody(): ?string {
		return $this->body;
	}

	public function setBody( string $body ): self {
		$this->body = $body;
		return $this;
	}

	public function getLocalizedTitleKey(): ?string {
		return $this->localized_title_key;
	}

	public function setLocalizedTitleKey( string $key ): self {
		$this->localized_title_key = $key;
		return $this;
	}

	public function getLocalizedTitleArgs(): ?array {
		return $this->localized_title_args;
	}

	public function setLocalizedTitleArgs( array $args ): self {
		$this->localized_title_args = $args;
		return $this;
	}

	public function getLocalizedActionKey(): ?string {
		return $this->localized_action_key;
	}

	public function setLocalizedActionKey( string $key ): self {
		$this->localized_action_key = $key;
		return $this;
	}

	public function getLocalizedMessageKey(): ?string {
		return $this->localized_message_key;
	}

	public function setLocalizedMessageKey( string $key ): self {
		$this->localized_message_key = $key;
		return $this;
	}

	public function getLocalizedMessageArgs(): ?array {
		return $this->localized_message_args;
	}

	public function setLocalizedMessageArgs( array $args ): self {
		$this->localized_message_args = $args;
		return $this;
	}

	public function getLaunchImage(): ?string {
		return $this->launch_image;
	}

	public function setLaunchImage( string $name ): self {
		$this->launch_image = $name;
		return $this;
	}

	public function jsonSerialize() {

		$data = [
			'title' => $this->title,
			'body' => $this->body,
			'title-loc-key' => $this->localized_title_key,
			'title-loc-args' => $this->localized_title_args,
			'action-loc-key' => $this->localized_action_key,
			'loc-key' => $this->localized_message_key,
			'loc-args' => $this->localized_message_args,
			'launch-image' => $this->launch_image,
		];

		$output = array_filter(
			$data, function( $value ) {
				return ! is_null( $value );
			}
		);

		// If only the title is present, return it instead of an object
		if ( count( $output ) === 1 && array_keys( $output )[0] === 'title' ) {
			return $this->title;
		}

		return $output;
	}
}
