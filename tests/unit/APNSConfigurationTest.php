<?php
declare( strict_types = 1 );
class APNSConfigurationTest extends APNSTest {

	public function testThatProductionInitializerUsesProductionEnvironment() {
		$config = APNSConfiguration::production( $this->new_credentials() );
		$this->assertEquals( $config->get_environment(), APNSConfiguration::APNS_ENVIRONMENT_PRODUCTION );
	}

	public function testThatProductionEndpointIsCorrect() {
		$config = APNSConfiguration::production( $this->new_credentials() );
		$this->assertEquals( $config->get_endpoint(), APNSConfiguration::APNS_ENDPOINT_PRODUCTION );
	}

	public function testThatSandboxInitializerUsesSandboxEnvironment() {
		$config = APNSConfiguration::sandbox( $this->new_credentials() );
		$this->assertEquals( $config->get_environment(), APNSConfiguration::APNS_ENVIRONMENT_SANDBOX );
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
		$config    = $this->new_configuration()->set_user_agent( $useragent );
		$this->assertEquals( $useragent, $config->get_user_agent() );
	}

	public function testThatDefaultRefreshIntervalIs30Minutes() {
		$this->assertEquals( 1800, $this->new_configuration()->get_token_refresh_interval() );
	}

	public function testThatRefreshIntervalSetterWorks() {
		$interval = random_int( 1260, 3540 );
		$config   = $this->new_configuration()->set_token_refresh_interval( $interval );
		$this->assertEquals( $interval, $config->get_token_refresh_interval() );
	}

	public function testThatRefreshIntervalSetterThrowsForValuesLessThan21Minutes() {
		$this->expectException( InvalidArgumentException::class );
		$this->new_configuration()->set_token_refresh_interval( 1259 );
	}

	public function testThatRefreshIntervalSetterThrowsForValuesGreaterThan59Minutes() {
		$this->expectException( InvalidArgumentException::class );
		$this->new_configuration()->set_token_refresh_interval( 3541 );
	}

	public function testThatGetProviderTokenFetchesValidToken() {
		$token   = $this->random_string();
		$factory = $this->new_token_factory_with_mocked_token( $token );
		$config  = APNSConfiguration::sandbox( $this->new_credentials(), $factory );
		$this->assertEquals( $token, $config->get_provider_token() );
	}

	public function testThatGetProviderTokenFetchesCachedToken() {
		$token   = $this->random_string();
		$factory = $this->new_token_factory_with_mocked_token( $token );
		$config  = APNSConfiguration::sandbox( $this->new_credentials(), $factory );
		$this->assertEquals( $token, $config->get_provider_token() );
		$this->assertEquals( $token, $config->get_provider_token() );
	}
}
