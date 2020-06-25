<?php
declare( strict_types = 1 );

require_once( 'vendor/autoload.php' );

echo '=== Push Notification Server ===' . PHP_EOL;

// var_dump(getenv('USER_AGENT'));
// die();

$auth = new APNSCredentials( getenv( 'KEY_ID' ), getenv( 'TEAM_ID' ), getenv( 'KEY' ) );
$configuration = APNSConfiguration::production( $auth );
// $configuration->setUserAgent( getenv( 'USER_AGENT' ) );
$client = new APNSClient( $configuration );
// $client->setDebug(true);

echo "\t Connected.\n";

$token = getenv( 'TOKEN' );
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
$tokens_to_delete = [];

foreach ( $responses as $response ) {
	if ( $response->isUnrecoverableError() ) {
		$notifications_to_delete[] = $response->getUuid();
	} elseif ( $response->shouldRetry() ) {
		$notifications_to_retry[] = $response->getUuid();
	} elseif ( $response->shouldUnsubscribeDevice() ) {
		$tokens_to_delete[] = $response->getToken();
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

echo '=== Tokens to Delete ===' . PHP_EOL;
foreach ( $tokens_to_delete as $token ) {
	echo "\t$token" . PHP_EOL;
}

$client->close();
