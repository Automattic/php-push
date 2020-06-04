<?php
declare( strict_types = 1 );

interface APNSTokenFactory {
	public function get_token( $team_id, $key_id, $key_bytes ) : string;
}
