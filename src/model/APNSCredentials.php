<?php
declare( strict_types = 1 );

class APNSCredentials {

	/** @var string */
	private $key_id;

	/** @var string */
	private $team_id;

	/** @var string */
	private $key_bytes;

	function __construct( string $key_id, string $team_id, string $key_bytes ) {

		if ( strlen( $key_id ) !== 10 ) {
			throw new InvalidArgumentException( 'Invalid key identifier: ' . $key_id . '. Key IDs must be 10 characters long (Found ' . strlen( $key_id ) . ').' );
		}

		if ( strlen( $team_id ) !== 10 ) {
			throw new InvalidArgumentException( 'Invalid team identifier: ' . $team_id . '. Team IDs must be 10 characters long' );
		}

		$this->key_id = $key_id;
		$this->team_id = $team_id;
		$this->key_bytes = $key_bytes;
	}

	function getKeyId(): string {
		return $this->key_id;
	}

	function getTeamId(): string {
		return $this->team_id;
	}

	function getKeyBytes(): string {
		return $this->key_bytes;
	}
}
