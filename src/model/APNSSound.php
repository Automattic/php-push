<?php
declare( strict_types = 1 );

// Keys defined at https://developer.apple.com/documentation/usernotifications/setting_up_a_remote_notification_server/generating_a_remote_notification#2990112
class APNSSound implements JsonSerializable {

	private $is_critical = 0;
	private $name;
	private $volume;

	function __construct( string $name, float $volume = 1.0, bool $is_critical = false ) {
		$this->name = $name;
		$this->volume = $volume;
		$this->setIsCritical( $is_critical );
	}

	function getIsCritical(): bool {
		return $this->is_critical === 1;
	}

	function setIsCritical( bool $is_critical ) {
		$this->is_critical = $is_critical ? 1 : 0;
		return $this;
	}

	function getName(): string {
		return $this->name;
	}

	function setName( string $name ) {
		$this->name = $name;
		return $this;
	}

	function getVolume(): float {
		return $this->volume;
	}

	function setVolume( float $volume ) {
		if ( $volume < 0 || $volume > 1.0 ) {
			throw new InvalidArgumentException( 'Invalid sound volume: ' . $volume . '. Valid volume levels are between 0.0 and 1.0' );
		}

		$this->volume = $volume;
		return $this;
	}

	function jsonSerialize() {
		return [
			'critical' => $this->is_critical,
			'name' => $this->name,
			'volume' => $this->volume,
		];
	}
}
