<?php
declare( strict_types = 1 );

require_once dirname( __DIR__ ) . '/vendor/autoload.php';
require_once __DIR__ . '/db-connect.php';

echo '=== Push Notification Server ===' . PHP_EOL;

$kid = strval( getenv( 'KEY_ID' ) );
$tid = strval( getenv( 'TEAM_ID' ) );
$key = strval( getenv( 'KEY' ) );

$auth = new APNSCredentials( $kid, $tid, $key );
$configuration = APNSConfiguration::production( $auth );
$configuration->setUserAgent( 'wordpress.com development' );

$client = new APNSClient( $configuration );

echo "\t Connected.\n";

while ( true ) {
	$start = microtime( true );

	$pushes = get_request();

	$time = microtime( true ) - $start;
	echo PHP_EOL . '=== Fetched ' . count( $pushes ) . ' Messages in ' . $time . ' seconds === ' . PHP_EOL;

	$client->sendRequests( $pushes->all() );

	$time = microtime( true ) - $start;
	echo PHP_EOL . '=== Done in ' . $time . ' seconds === ' . PHP_EOL;
}

$client->close();
