<?php
declare( strict_types = 1 );

use \Firebase\JWT\JWT;

class APNSDefaultTokenFactory implements APNSTokenFactory {
	public function get_token( string $team_id, string $key_id, string $key_bytes ): string {
		$payload = [
			'iss' => $team_id,
			'iat' => time(),
		];

		return JWT::encode( $payload, $key_bytes, 'ES256', $key_id );
	}
}
