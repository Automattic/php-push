<?php
declare( strict_types = 1 );

use \Firebase\JWT\JWT;

class APNSDefaultTokenFactory implements APNSTokenFactory {

	private $credentials;

	public function __construct( APNSCredentials $credentials ) {
		$this->credentials = $credentials;
	}

	public function get_token( $team_id, $key_id, $key_bytes ): string {
		$payload = [
			'iss' => $team_id,
			'iat' => time(),
		];

		return JWT::encode( $payload, $key_bytes, 'ES256', $key_id );
	}
}
