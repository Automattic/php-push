<?php
declare( strict_types = 1 );

// Keys defined at https://developer.apple.com/documentation/usernotifications/setting_up_a_remote_notification_server/generating_a_remote_notification#2990112
class APNSSound implements JsonSerializable {

	/** @var bool */
	private $is_critical = false;

	/** @var string */
	private $name;

	/** @var float */
	private $volume;

	function __construct( string $name, float $volume = 1.0, bool $is_critical = false ) {
		$this->name = $name;
		$this->volume = $volume;
		$this->setIsCritical( $is_critical );
	}

	function getIsCritical(): bool {
		return $this->is_critical;
	}

	function setIsCritical( bool $is_critical ): self {
		$this->is_critical = $is_critical;
		return $this;
	}

	function getName(): string {
		return $this->name;
	}

	function setName( string $name ): self {
		$this->name = $name;
		return $this;
	}

	function getVolume(): float {
		return $this->volume;
	}

	function setVolume( float $volume ): self {
		if ( $volume < 0 || $volume > 1.0 ) {
			throw new InvalidArgumentException( 'Invalid sound volume: ' . $volume . '. Valid volume levels are between 0.0 and 1.0' );
		}

		$this->volume = $volume;
		return $this;
	}

	function jsonSerialize() {
		return [
			'critical' => $this->is_critical ? 1 : 0,
			'name' => $this->name,
			'volume' => $this->volume,
		];
	}
}
