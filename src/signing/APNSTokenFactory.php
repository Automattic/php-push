<?php
declare( strict_types = 1 );

interface APNSTokenFactory {
	public function get_token( string $team_id, string $key_id, string $key_bytes ) : string;
}
