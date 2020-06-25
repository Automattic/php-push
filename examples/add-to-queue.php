<?php 

require_once dirname( __DIR__ ) . '/vendor/autoload.php';
require_once __DIR__ . '/db-connect.php';

global $capsule;


while(true) {
	$notifications = [];

	$token = bin2hex(random_bytes(32));
	saveToken($token);

	for($i = 0; $i < 2; $i++) {
		$message = bin2hex(random_bytes(random_int(2, 2048)));
		$alert = new APNSAlert('Title', $message);
		$payload = new APNSPayload($alert);

		$request = APNSRequest::fromPayload($payload, $token, new APNSRequestMetadata(getenv('topic')));
		savePush($request);
	}
}
