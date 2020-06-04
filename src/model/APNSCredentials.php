<?php
declare( strict_types = 1 );

class APNSCredentials {

	private $key_id;
	private $team_id;
	private $key_bytes;

	function __construct( $key_id, $team_id, $key_bytes ) {

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

	function getKeyId() {
		return $this->key_id;
	}

	function getTeamId() {
		return $this->team_id;
	}

	function getKeyBytes() {
		return $this->key_bytes;
	}
}
