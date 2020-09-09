<?php
declare( strict_types = 1 );
// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped

require_once __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;

Dotenv::createImmutable( __DIR__ )->load();

echo '=== Push Notification Server ===' . PHP_EOL;

$key_id  = strval( getenv( 'KEY_ID' ) );
$team_id = strval( getenv( 'TEAM_ID' ) );
$key     = strval( getenv( 'KEY' ) );

$auth          = new APNSCredentials( $key_id, $team_id, $key );
$configuration = APNSConfiguration::sandbox( $auth );
$configuration->set_user_agent( strval( getenv( 'USER_AGENT' ) ) );
$client = new APNSClient( $configuration );
$client->set_debug( true );

echo "\t Connected.\n";
$notification_title   = 'Title';
$notification_message = 'Timestamp ' . time();
echo "\t Will send notification with:\n";
echo "\t - Title: $notification_title \n";
echo "\t - Message: " . $notification_message . PHP_EOL;

$token    = strval( getenv( 'TOKEN' ) );
$payload  = APNSPayload::from_alert( new APNSAlert( $notification_title, $notification_message ) );
$metadata = new APNSRequestMetadata( strval( getenv( 'TOPIC' ) ) );
$request  = APNSRequest::from_payload( $payload, $token, $metadata );

$requests = [];

// Send 3 Requests
foreach ( range( 0, 2 ) as $i ) {
	$requests[] = $request;
}

$responses = $client->send_requests( $requests );

$notifications_to_retry  = [];
$notifications_to_delete = [];

foreach ( $responses as $response ) {
	if ( $response->is_unrecoverable_error() ) {
		$notifications_to_delete[] = $response->get_uuid();
	} elseif ( $response->should_retry() ) {
		$notifications_to_retry[] = $response->get_uuid();
	}
}

echo '=== Requests to Delete ===' . PHP_EOL;
foreach ( $notifications_to_delete as $uuid ) {
	echo "\t$uuid" . PHP_EOL;
}

echo '=== Requests to Retry ===' . PHP_EOL;
foreach ( $notifications_to_retry as $uuid ) {
	echo "\t$uuid" . PHP_EOL;
}

$client->close();
