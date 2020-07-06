<?php
declare( strict_types = 1 );

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Support\Collection;
use Dotenv\Dotenv;

Dotenv::createImmutable( dirname( __DIR__ ) )->load();

$capsule = new Capsule;
$capsule->addConnection(
	[
		'driver' => 'mysql',
		'host' => 'localhost',
		'database' => getenv( 'DATABASE' ),
		'username' => getenv( 'DB_USERNAME' ),
		'password' => getenv( 'DB_PASSWORD' ),
		'charset' => 'utf8',
		'collation' => 'utf8_unicode_ci',
		'prefix' => '',
	]
);

// Make this Capsule instance available globally via static methods... (optional)
$capsule->setAsGlobal();

function save_request( APNSRequest $request ): void {
	global $capsule;

	$capsule->table( 'mobile_push_queue' )->insert(
		[
			'token' => $request->getToken(),
			'payload' => $request->toJSON(),
			'when' => gmdate( 'Y-m-d H:i:s' ),
			'mobile_push_client_id' => get_client_id(),
			'worker_id' => random_int( 1, 16 ),
		]
	);
}

function get_request( int $count = 10 ): Collection {
	global $capsule;
	return $capsule->table( 'mobile_push_queue' )->limit( $count )->get();
}

function save_token( string $token ): void {
	global $capsule;

	$capsule->table( 'mobile_push_tokens' )->insert(
		[
			'token' => $token,
			'user_id' => random_int( 1, 2147483647 ),
			'when' => gmdate( 'Y-m-d H:i:s' ),
			'active' => 1,
			'device_type' => [ 'apple', 'android' ][ random_int( 0, 1 ) ],
			'production' => random_int( 0, 1 ),
			'meta' => '',
			'mobile_push_client_id' => get_client_id(),
		]
	);
}

function get_client_id(): int {
	return random_int( 0, 10 );
}
