<?php
declare( strict_types = 1 );

// Keys defined at https://developer.apple.com/documentation/usernotifications/setting_up_a_remote_notification_server/generating_a_remote_notification#2943365
class APNSAlert implements JsonSerializable {

	/** @var array */
	protected $internal = [];

	function __construct( string $title, string $body ) {
		$this->internal['title'] = $title;
		$this->internal['body'] = $body;
	}

	function setTitle( string $title ): self {
		$this->internal['title'] = $title;
		return $this;
	}

	function setBody( string $body ): self {
		$this->internal['body'] = $body;
		return $this;
	}

	function setLocalizedTitleKey( string $key ): self {
		$this->internal['title-loc-key'] = $key;
		return $this;
	}

	function setLocalizedTitleArgs( array $args ): self {
		$this->internal['title-loc-args'] = $args;
		return $this;
	}

	function setLocalizedActionKey( string $key ): self {
		$this->internal['action-loc-key'] = $key;
		return $this;
	}

	function setLocalizedMessageKey( string $key ): self {
		$this->internal['loc-key'] = $key;
		return $this;
	}

	function setLocalizedMessageArgs( array $args ): self {
		$this->internal['loc-args'] = $args;
		return $this;
	}

	function setLaunchImage( string $name ): self {
		$this->internal['launch-image'] = $name;
		return $this;
	}

	function jsonSerialize() {
		return $this->internal;
	}
}
