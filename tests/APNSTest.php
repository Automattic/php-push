<?php
declare( strict_types = 1 );
use PHPUnit\Framework\TestCase;

abstract class APNSTest extends TestCase {
	public function tearDown(): void {
		Mockery::close();
	}

	protected function assertKeyIsNotPresentForObject( $key, object $object ) {
		$this->assertNotNull( $object );
		$object = $this->encode( $object );
		$this->assertFalse( property_exists( $object, $key ) );
	}

	function random_string( $length = 32 ) {
		$hex = bin2hex( random_bytes( $length ) );
		$string = substr( $hex, 0, $length );
		return $string;
	}

	function random_uuid() {
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
	function new_sound(): APNSSound {
		return new APNSSound( $this->random_string() );
	}

	function new_alert(): APNSAlert {
		return new APNSAlert( $this->random_string(), $this->random_string() );
	}

	function new_request( $payload = null, string $token = null, ?APNSRequestMetadata $metadata = null ) {

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

	function new_metadata( ?string $topic = null, ?string $uuid = null ): APNSRequestMetadata {

		if ( is_null( $topic ) ) {
			$topic = $this->random_string();
		}

		if ( is_null( $uuid ) ) {
			$uuid = $this->random_uuid();
		}

		return new APNSRequestMetadata( $topic, $uuid );
	}

	function new_payload(): APNSPayload {
		return new APNSPayload( $this->new_alert() );
	}

	function new_configuration(): APNSConfiguration {
		$factory = Mockery::mock( APNSTokenFactory::class );
		$factory->allows(
			[
				'get_token' => $this->random_string(),
			]
		);

		return $this->new_configuration_with_token_factory( $factory );
	}

	function new_configuration_with_token_factory( APNSTokenFactory $factory ): APNSConfiguration {
		return APNSConfiguration::production( $this->new_credentials(), $factory );
	}

	function new_token_factory_with_mocked_token( string $token ): APNSTokenFactory {
		$factory = Mockery::mock( APNSTokenFactory::class );
		$factory->shouldReceive( 'get_token' )->andReturn( $token )->once();
		return $factory;
	}

	function new_configuration_with_mocked_provider_token( string $token ): APNSConfiguration {
		return $this->new_configuration_with_token_factory( $this->new_token_factory_with_mocked_token( $token ) );
	}

	function new_configuration_with_mocked_endpoint( string $endpoint ): APNSConfiguration {
		$factory = Mockery::mock( APNSConfiguration::class );
		$factory->shouldReceive( 'get_endpoint' )->andReturn( $endpoint )->once();
		return $factory;
	}

	function new_sandbox_configuration(): APNSConfiguration {
		return APNSConfiguration::sandbox( $this->new_credentials() );
	}

	function new_credentials(): APNSCredentials {
		return new APNSCredentials( $this->random_string( 10 ), $this->random_string( 10 ), '' );
	}

	function encode( $object ) {
		return json_decode( json_encode( $object ) );
	}

	function encode_to_array( $object ) {
		return json_decode( json_encode( $object ), true );
	}

	function from_json( string $string ): object {
		return json_decode( $string );
	}

	function json_without( string $string, string $key ): string {
		$object = json_decode( $string );
		unset( $object->$key );
		return json_encode( $object );
	}

	function json_adding( string $string, string $key, $value ): string {
		$object = json_decode( $string );
		$object->$key = $value;
		return json_encode( $object );
	}

	function decode( string $string ): object {
		return json_decode( $string );
	}

	function replace_object_key_with_value( $object, $key, $value ) {
		$class = new ReflectionClass( get_class( $object ) );

		$property = $class->getProperty( $key );
		$property->setAccessible( true );
		$property->setValue( $object, $value );

		return $object;
	}

	function get_test_resource( $key ): string {
		return file_get_contents( 'tests/resources/' . $key . '.txt' );
	}
}
