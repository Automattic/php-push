<?php

$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

use Illuminate\Database\Capsule\Manager as Capsule;

$capsule = new Capsule;
$capsule->addConnection([
    'driver'    => 'mysql',
    'host'      => 'localhost',
    'database'  => getenv('DATABASE'),
    'username'  => getenv('DB_USERNAME'),
    'password'  => getenv('DB_PASSWORD'),
    'charset'   => 'utf8',
    'collation' => 'utf8_unicode_ci',
    'prefix'    => '',
]);

// Make this Capsule instance available globally via static methods... (optional)
$capsule->setAsGlobal();

function savePush(APNSRequest $request) {
	global $capsule;

	$capsule->table('mobile_push_queue')->insert([
		'token'					=> $request->getToken(),
		'payload'				=> $request->toJSON(),
		'when'					=> date("Y-m-d H:i:s"),
		'mobile_push_client_id'	=> getClientId(),
		'worker_id'				=> random_int(1,16)
	]);
}

function getPushes($count = 10) {
	global $capsule;
	return $capsule->table('mobile_push_queue')->limit($count)->get();
}

function saveToken(string $token) {
	global $capsule;

	$capsule->table('mobile_push_tokens')->insert([
		'token'					=> $token,
		'user_id'				=> random_int(1, pow(2,31)),
		'when'					=> date("Y-m-d H:i:s"),
		'active'				=> 1,
		'device_type'			=> ['apple', 'android'][random_int(0,1)],
		'production'			=> random_int(0,1),
		'meta'					=> '',
		'mobile_push_client_id'	=> getClientId()
	]);
}

function getClientId() {
	return random_int(0, 10);
}