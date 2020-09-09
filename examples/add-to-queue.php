<?php
declare( strict_types = 1 );

require_once dirname( __DIR__ ) . '/vendor/autoload.php';
require_once __DIR__ . '/db-connect.php';

$topic = (string) getenv( 'topic' );

while ( true ) {
	$notifications = [];

	$token = bin2hex( random_bytes( 32 ) );
	save_token( $token );

	for ( $i = 0; $i < 2; $i++ ) {
		$message = bin2hex( random_bytes( random_int( 2, 2048 ) ) );
		$alert   = new APNSAlert( 'Title', $message );
		$payload = APNSPayload::from_alert( $alert );

		$request = APNSRequest::from_payload( $payload, $token, new APNSRequestMetadata( $topic ) );
		save_request( $request );
	}
}
