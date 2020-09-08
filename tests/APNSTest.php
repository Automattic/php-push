<?php
declare( strict_types = 1 );
use PHPUnit\Framework\TestCase;

abstract class APNSTest extends TestCase {
	public function tearDown(): void {
		Mockery::close();
	}

	protected function assertKeyIsNotPresentForObject( $key, object $object ) {
		$this->assertNotNull( $object );
		$object = $this->to_stdclass( $object );
		$this->assertFalse( property_exists( $object, $key ) );
	}

	protected function random_string( $length = 32 ) {
		$hex = bin2hex( random_bytes( $length ) );
		$string = substr( $hex, 0, $length );
		return $string;
	}

	protected function random_uuid() {
		return $this->random_string( 8 ) .
		'-' .
		$this->random_string( 4 ) .
		'-' .
		$this->random_string( 4 ) .
		'-' .
		$this->random_string( 4 ) .
		'-' .
		$this->random_string( 12 );
	}

	// Fixtures
	protected function new_sound(): APNSSound {
		return new APNSSound( $this->random_string() );
	}

	protected function new_alert(): APNSAlert {
		return new APNSAlert( $this->random_string(), $this->random_string() );
	}

	protected function new_request( $payload = null, string $token = null, ?APNSRequestMetadata $metadata = null ) {

		if ( is_null( $payload ) ) {
			$payload = $this->new_payload();
		}

		if ( is_null( $token ) ) {
			$token = $this->random_string();
		}

		if ( is_null( $metadata ) ) {
			$metadata = $this->new_metadata();
		}

		return APNSRequest::fromPayload( $payload, $token, $metadata );
	}

	protected function new_request_from_token( string $token ): APNSRequest {
		return $this->new_request( null, $token, null );
	}

	protected function new_request_from_metadata( APNSRequestMetadata $meta ): APNSRequest {
		return $this->new_request( null, null, $meta );
	}

	protected function new_metadata( ?string $topic = null, ?string $uuid = null ): APNSRequestMetadata {

		if ( is_null( $topic ) ) {
			$topic = $this->random_string();
		}

		if ( is_null( $uuid ) ) {
			$uuid = $this->random_uuid();
		}

		return new APNSRequestMetadata( $topic, $uuid );
	}

	protected function new_payload(): APNSPayload {
		return APNSPayload::fromAlert( $this->new_alert() );
	}

	protected function new_configuration(): APNSConfiguration {
		$factory = Mockery::mock( APNSTokenFactory::class );
		$factory->allows(
			[
				'get_token' => $this->random_string(),
			]
		);

		return $this->new_configuration_with_token_factory( $factory );
	}

	protected function new_configuration_with_token_factory( APNSTokenFactory $factory ): APNSConfiguration {
		return APNSConfiguration::production( $this->new_credentials(), $factory );
	}

	protected function new_token_factory_with_mocked_token( string $token ): APNSTokenFactory {
		$factory = Mockery::mock( APNSTokenFactory::class );
		$factory->shouldReceive( 'get_token' )->andReturn( $token )->once();
		return $factory;
	}

	protected function new_configuration_with_mocked_provider_token( string $token ): APNSConfiguration {
		return $this->new_configuration_with_token_factory( $this->new_token_factory_with_mocked_token( $token ) );
	}

	protected function new_configuration_with_mocked_endpoint( string $endpoint ): APNSConfiguration {
		$factory = Mockery::mock( APNSConfiguration::class );
		$factory->shouldReceive( 'get_endpoint' )->andReturn( $endpoint )->once();
		return $factory;
	}

	protected function new_sandbox_configuration(): APNSConfiguration {
		return APNSConfiguration::sandbox( $this->new_credentials() );
	}

	protected function new_credentials(): APNSCredentials {
		return new APNSCredentials( $this->random_string( 10 ), $this->random_string( 10 ), '' );
	}

	protected function to_stdclass( $object ): object {
		if ( is_a( $object, APNSPayload::class ) ) {
			return $this->from_json( $object->toJSON() );
		}

		return json_decode( json_encode( $object ) );
	}

	protected function to_string( $object ): string {
		return json_decode( json_encode( $object ) );
	}

	protected function new_apns_http_failure_response( int $status_code, string $reason = 'not read here' ): string {
		return <<<TEXT
HTTP/2 $status_code
apns-id: 8FE746FE-1112-2966-3590-2DC3F038536B

{"reason":"$reason"}
TEXT;
	}

	protected function from_json( string $string ): object {
		return json_decode( $string );
	}

	protected function json_without( string $string, string $key ): string {
		$object = json_decode( $string );
		unset( $object->$key );
		return json_encode( $object );
	}

	protected function json_adding( string $string, string $key, $value ): string {
		$object = json_decode( $string );
		$object->$key = $value;
		return json_encode( $object );
	}

	protected function decode( string $string ): object {
		return json_decode( $string );
	}

	protected function replace_object_key_with_value( $object, $key, $value ) {
		$class = new ReflectionClass( get_class( $object ) );

		$property = $class->getProperty( $key );
		$property->setAccessible( true );
		$property->setValue( $object, $value );

		return $object;
	}

	protected function get_test_resource( $key ): string {
		return file_get_contents( 'tests/resources/' . $key . '.txt' );
	}
}
