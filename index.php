<?php

require_once('vendor/autoload.php');

echo '=== Push Notification Server ===' . PHP_EOL;

$key_id = getenv('KEY_ID');
$team_id = getenv('TEAM_ID');

$auth = new APNSCredentials($key_id, $team_id, getenv('KEY'));
$configuration = APNSConfiguration::production($auth);
$configuration->setUserAgent('wordpress.com development');
$client = new APNSClient( $configuration );

echo "\t Connected.\n";

$messages = [];

while(true) {
	$start = microtime(true);

	$client->sendPushes($messages);

	$time = microtime(true) - $start;
	echo PHP_EOL . '=== Done in ' . $time . ' seconds === ' . PHP_EOL;
}

$client->close();
