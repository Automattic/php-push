<?php

require_once '../vendor/autoload.php';
require_once 'db-connect.php';
global $capsule;

echo '=== Push Notification Server ===' . PHP_EOL;

$kid = getenv('KEY_ID');
$tid = getenv('TEAM_ID');
$key = getenv('KEY');

$auth = new APNSCredentials($kid, $tid, $key);
$configuration = APNSConfiguration::production($auth);
$configuration->setUserAgent('wordpress.com development');

$client = new APNSClient( $configuration );

echo "\t Connected.\n";

while(true) {
	$start = microtime(true);

	$pushes = getPushes();

	$time = microtime(true) - $start;
	echo PHP_EOL . '=== Fetched ' . count($pushes) . ' Messages in ' . $time . ' seconds === ' . PHP_EOL;

	$client->sendPayloads($pushes->all());

	$time = microtime(true) - $start;
	echo PHP_EOL . '=== Done in ' . $time . ' seconds === ' . PHP_EOL;
}



$client->close();
