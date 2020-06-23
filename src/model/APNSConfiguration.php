<?php
declare( strict_types = 1 );

class APNSConfiguration {

	public const APNS_ENVIRONMENT_PRODUCTION = 'production';
	public const APNS_ENDPOINT_PRODUCTION = 'https://api.push.apple.com/3/device/';

	public const APNS_ENVIRONMENT_SANDBOX = 'sandbox';
	public const APNS_ENDPOINT_SANDBOX = 'https://api.development.push.apple.com/3/device/';

	private $credentials;
	private $topic;
	private $user_agent = null;
	private $environment;

	// How frequently to refresh the auth token. Specified in seconds.
	private $token_refresh_interval = 1800;

	private $token_factory;

	protected function __construct( APNSCredentials $credentials, string $environment, ?APNSTokenFactory $factory ) {
		$this->credentials = $credentials;
		$this->environment = $environment;
		$this->token_factory = $factory ?? new APNSDefaultTokenFactory( $credentials );
	}

	static function production( APNSCredentials $credentials, ?APNSTokenFactory $factory = null ) {
		return new APNSConfiguration( $credentials, APNSConfiguration::APNS_ENVIRONMENT_PRODUCTION, $factory );
	}

	static function sandbox( APNSCredentials $credentials, ?APNSTokenFactory $factory = null ) {
		return new APNSConfiguration( $credentials, APNSConfiguration::APNS_ENVIRONMENT_SANDBOX, $factory );
	}

	function getEnvironment() {
		return $this->environment;
	}

	private $current_token = null;
	private $expires = 0;

	function getProviderToken() {

		if ( ! is_null( $this->current_token ) && $this->expires > time() ) {
			return $this->current_token;
		}

		$current_token = $this->token_factory->get_token(
			$this->credentials->getTeamId(),
			$this->credentials->getKeyId(),
			$this->credentials->getKeyBytes()
		);

		$this->expires = time() + $this->token_refresh_interval;
		$this->current_token = $current_token;

		return $current_token;
	}

	function getUserAgent(): ?string {
		return $this->user_agent;
	}

	function setUserAgent( string $user_agent ) {
		$this->user_agent = $user_agent;
		return $this;
	}

	function getTokenRefreshInterval(): int {
		return $this->token_refresh_interval;
	}

	// Must be between 20 and 60 minutes:
	// Refresh your token no more than once every 20 minutes and no less than once every 60 minutes. APNs rejects any request whose token contains a timestamp that is more than one hour old. Similarly, APNs reports an error if you recreate your tokens more than once every 20 minutes.
	// Source: https://developer.apple.com/documentation/usernotifications/setting_up_a_remote_notification_server/establishing_a_token-based_connection_to_apns
	function setTokenRefreshInterval( int $interval ) {

		if ( $interval < 1260 ) {
			throw new InvalidArgumentException( 'Invalid Token Refresh interval: ' . $interval . '. It must be greater than 21 minutes and less than 59 minutes (specified in seconds)' );
		}

		if ( $interval > 3540 ) {
			throw new InvalidArgumentException( 'Invalid Token Refresh interval: ' . $interval . '. It must be less than 59 minutes and greater than 21 minutes (specified in seconds)' );
		}

		$this->token_refresh_interval = $interval;

		return $this;
	}

	function get_endpoint() {
		if ( $this->environment === APNSConfiguration::APNS_ENVIRONMENT_PRODUCTION ) {
			return APNSConfiguration::APNS_ENDPOINT_PRODUCTION;
		}

		if ( $this->environment === APNSConfiguration::APNS_ENVIRONMENT_SANDBOX ) {
			return APNSConfiguration::APNS_ENDPOINT_SANDBOX;
		}

		throw new OutOfBoundsException( 'Unable to determine endpoint for environment' );
	}
}
