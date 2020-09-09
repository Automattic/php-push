<?php
declare( strict_types = 1 );

class APNSCredentials {

	/** @var string */
	private $key_id;

	/** @var string */
	private $team_id;

	/** @var string */
	private $key_bytes;

	public function __construct( string $key_id, string $team_id, string $key_bytes ) {

		if ( strlen( $key_id ) !== 10 ) {
			throw new InvalidArgumentException( 'Invalid key identifier: ' . $key_id . '. Key IDs must be 10 characters long (Found ' . strlen( $key_id ) . ').' );
		}

		if ( strlen( $team_id ) !== 10 ) {
			throw new InvalidArgumentException( 'Invalid team identifier: ' . $team_id . '. Team IDs must be 10 characters long' );
		}

		$this->key_id    = $key_id;
		$this->team_id   = $team_id;
		$this->key_bytes = $key_bytes;
	}

	public function get_key_id(): string {
		return $this->key_id;
	}

	public function get_team_id(): string {
		return $this->team_id;
	}

	public function get_key_bytes(): string {
		return $this->key_bytes;
	}
}
