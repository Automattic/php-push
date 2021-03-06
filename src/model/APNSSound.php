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

	public function __construct( string $name, float $volume = 1.0, bool $is_critical = false ) {
		$this->name   = $name;
		$this->volume = $volume;
		$this->set_is_critical( $is_critical );
	}

	public static function from_string( string $name ): APNSSound {
		return new APNSSound( $name );
	}

	public function get_is_critical(): bool {
		return $this->is_critical;
	}

	public function set_is_critical( bool $is_critical ): self {
		$this->is_critical = $is_critical;
		return $this;
	}

	public function get_name(): string {
		return $this->name;
	}

	public function set_name( string $name ): self {
		$this->name = $name;
		return $this;
	}

	public function get_volume(): float {
		return $this->volume;
	}

	public function set_volume( float $volume ): self {
		if ( $volume < 0 || $volume > 1.0 ) {
			throw new InvalidArgumentException( 'Invalid sound volume: ' . $volume . '. Valid volume levels are between 0.0 and 1.0' );
		}

		$this->volume = $volume;
		return $this;
	}

	/** @psalm-suppress InvalidReturnType */
	public function jsonSerialize() {

		// If the volume and `critical` flags haven't been modified, we can just send the filename to save space
		if ( 1.0 === $this->volume && false === $this->is_critical ) {
			return $this->name;
		}

		return [
			'critical' => $this->is_critical ? 1 : 0,
			'name'     => $this->name,
			'volume'   => $this->volume,
		];
	}
}
