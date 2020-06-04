<?php
declare( strict_types = 1 );
class APNSConfigurationTest extends APNSTest {

	public function testThatProductionInitializerUsesProductionEnvironment() {
		$config = APNSConfiguration::production( $this->new_credentials() );
		$this->assertEquals( $config->getEnvironment(), APNSConfiguration::APNS_ENVIRONMENT_PRODUCTION );
	}

	public function testThatProductionEndpointIsCorrect() {
		$config = APNSConfiguration::production( $this->new_credentials() );
		$this->assertEquals( $config->get_endpoint(), APNSConfiguration::APNS_ENDPOINT_PRODUCTION );
	}

	public function testThatSandboxInitializerUsesSandboxEnvironment() {
		$config = APNSConfiguration::sandbox( $this->new_credentials() );
		$this->assertEquals( $config->getEnvironment(), APNSConfiguration::APNS_ENVIRONMENT_SANDBOX );
	}

	public function testThatSandboxEndpointIsCorrect() {
		$config = APNSConfiguration::sandbox( $this->new_credentials() );
		$this->assertEquals( $config->get_endpoint(), APNSConfiguration::APNS_ENDPOINT_SANDBOX );
	}

	// This should be impossible to hit, but just in case, we'll make sure it works
	public function testThatGetEndpointThrowsForInvalidConfiguration() {
		$config = APNSConfiguration::sandbox( $this->new_credentials() );
		$this->replace_object_key_with_value( $config, 'environment', $this->random_string() );
		$this->expectException( OutOfBoundsException::class );
		$config->get_endpoint();
	}

	public function testThatUserAgentSetterWorks() {
		$useragent = $this->random_string();
		$config = $this->new_configuration()->setUserAgent( $useragent );
		$this->assertEquals( $useragent, $config->getUserAgent() );
	}

	public function testThatDefaultRefreshIntervalIs30Minutes() {
		$this->assertEquals( 1800, $this->new_configuration()->getTokenRefreshInterval() );
	}

	public function testThatRefreshIntervalSetterWorks() {
		$interval = random_int( 1260, 3540 );
		$config = $this->new_configuration()->setTokenRefreshInterval( $interval );
		$this->assertEquals( $interval, $config->getTokenRefreshInterval() );
	}

	public function testThatRefreshIntervalSetterThrowsForValuesLessThan21Minutes() {
		$this->expectException( InvalidArgumentException::class );
		$this->new_configuration()->setTokenRefreshInterval( 1259 );
	}

	public function testThatRefreshIntervalSetterThrowsForValuesGreaterThan59Minutes() {
		$this->expectException( InvalidArgumentException::class );
		$this->new_configuration()->setTokenRefreshInterval( 3541 );
	}

	public function testThatGetProviderTokenFetchesValidToken() {
		$token = $this->random_string();
		$factory = $this->new_token_factory_with_mocked_token( $token );
		$config = APNSConfiguration::sandbox( $this->new_credentials(), $factory );
		$this->assertEquals( $token, $config->getProviderToken() );
	}

	public function testThatGetProviderTokenFetchesCachedToken() {
		$token = $this->random_string();
		$factory = $this->new_token_factory_with_mocked_token( $token );
		$config = APNSConfiguration::sandbox( $this->new_credentials(), $factory );
		$this->assertEquals( $token, $config->getProviderToken() );
		$this->assertEquals( $token, $config->getProviderToken() );
	}
}
