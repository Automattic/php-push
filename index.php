<?php
declare( strict_types = 1 );

require_once __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;

Dotenv::createImmutable( __DIR__ )->load();

echo '=== Push Notification Server ===' . PHP_EOL;

$key_id = strval( getenv( 'KEY_ID' ) );
$team_id = strval( getenv( 'TEAM_ID' ) );
$key = strval( getenv( 'KEY' ) );

$auth = new APNSCredentials( $key_id, $team_id, $key );
$configuration = APNSConfiguration::production( $auth );
// $configuration->setUserAgent( getenv( 'USER_AGENT' ) );
$client = new APNSClient( $configuration );
// $client->setDebug(true);

echo "\t Connected.\n";

$token = strval( getenv( 'TOKEN' ) );
$payload = new APNSPayload( new APNSAlert( 'Title', 'Message' ) );
$metadata = new APNSRequestMetadata( 'org.WordPress' );
$request = APNSRequest::fromPayload( $payload, $token, $metadata );

$requests = [];

// Send 3 Requests
foreach ( range( 0, 2 ) as $i ) {
	$requests[] = $request;
}

$responses = $client->sendRequests( $requests );

$notifications_to_retry = [];
$notifications_to_delete = [];

foreach ( $responses as $response ) {
	if ( $response->isUnrecoverableError() ) {
		$notifications_to_delete[] = $response->getUuid();
	} elseif ( $response->shouldRetry() ) {
		$notifications_to_retry[] = $response->getUuid();
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
