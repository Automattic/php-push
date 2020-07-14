<?php
declare( strict_types = 1 );

interface MultiplexedNetworkService {

	// This function signature could be made tidier if it knew the details of
	// APNSRequest and APNSConfiguration.
	// I think it's better to keep this type ignorant about it and focused on
	// HTTP instead.
	//
	// Enqueue a request into the multiplexed queue.
	public function enqueueRequest( string $url, int $port, array $headers, string $body, bool $debug, bool $ssl_verification_enabled ): void;

	public function sendQueuedRequests(): array;

	public function execute( int &$still_running ): int;

	public function closeConnection(): void;
}
