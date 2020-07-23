<?php
declare( strict_types = 1 );

interface MultiplexedNetworkService {

	public function enqueueRequest( Request $request ): void;

	public function sendQueuedRequests(): array;

	public function execute( int &$still_running ): int;

	public function closeConnection(): void;
}
