<?php 

require_once '../vendor/autoload.php';
require_once 'db-connect.php';
global $capsule;


while(true) {
	$notifications = [];

	for($i = 0; $i < random_int(2,15); $i++) {
		$message = bin2hex(random_bytes(random_int(2, 2048)));
		$alert = new APNSAlert('Title', $message);
		savePush(new APNSPush($alert, getenv('TOPIC')), getenv('TOKEN'));
	}

	sleep(1);
}
