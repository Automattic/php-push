<?php
declare( strict_types = 1 );

use Dotenv\Dotenv;

$dotenv = Dotenv\Dotenv::createImmutable( dirname( __DIR__ ) );
$dotenv->load();

use Illuminate\Database\Capsule\Manager as Capsule;

$capsule = new Capsule;
$capsule->addConnection(
	[
		'driver' => 'mysql',
		'host' => 'localhost',
		'database' => getenv( 'DATABASE' ),
		'username' => getenv( 'USERNAME' ),
		'password' => getenv( 'PASSWORD' ),
		'charset' => 'utf8',
		'collation' => 'utf8_unicode_ci',
		'prefix' => '',
	]
);

// Make this Capsule instance available globally via static methods... (optional)
$capsule->setAsGlobal();

// phpcs:ignore NeutronStandard.Globals.DisallowGlobalFunctions.GlobalFunctions,WordPress.NamingConventions.ValidFunctionName.FunctionNameInvalid
function savePush( APNSPush $push, $token ) {
	global $capsule;

	$capsule->table( 'mobile_push_queue' )->insert(
		[
			'token' => $token,
			'payload' => json_encode( $push ),
			'when' => gmdate( 'Y-m-d H:i:s' ),
			'mobile_push_client_id' => 0,
		]
	);
}

// phpcs:ignore NeutronStandard.Globals.DisallowGlobalFunctions.GlobalFunctions,WordPress.NamingConventions.ValidFunctionName.FunctionNameInvalid
function getPushes( $count = 10 ) {
	global $capsule;
	return $capsule->table( 'mobile_push_queue' )->limit( $count )->get();
}
