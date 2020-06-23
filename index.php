<?php

require_once('vendor/autoload.php');

echo '=== Push Notification Server ===' . PHP_EOL;

$auth = new APNSCredentials(getenv('KEY_ID'), getenv('TEAM_ID'), getenv('KEY'));
$configuration = APNSConfiguration::production($auth);
$configuration->setUserAgent(getenv('USER_AGENT'));
$client = new APNSClient( $configuration );

echo "\t Connected.\n";

$token = getenv('TOKEN');
$payload = new APNSPayload( new APNSAlert("Title", "Message") );
$metadata = new APNSRequestMetadata('org.wordpress');
$request = APNSRequest::fromPayload($payload, $token, $metadata);

$client->sendRequests( [ $request ] );

$client->close();
