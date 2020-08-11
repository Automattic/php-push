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
$configuration = APNSConfiguration::sandbox( $auth );
$configuration->setUserAgent( strval( getenv( 'USER_AGENT' ) ) );
$client = new APNSClient( $configuration, new CurlMultiplexedNetworkService() );
$client->setDebug( true );

echo "\t Connected.\n";
$title = 'Title';
$message = 'Timestamp ' . time();
echo "\t Will send notification with:\n";
echo "\t - Title: " . $title . PHP_EOL;
echo "\t - Message: " . $message . PHP_EOL;

$token = strval( getenv( 'TOKEN' ) );
$payload = new APNSPayload( new APNSAlert( $title, $message ) );
$metadata = new APNSRequestMetadata( strval( getenv( 'TOPIC' ) ) );
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
