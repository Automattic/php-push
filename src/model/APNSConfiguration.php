<?php
declare( strict_types = 1 );

class APNSConfiguration {

	public const APNS_ENVIRONMENT_PRODUCTION = 'production';
	public const APNS_ENDPOINT_PRODUCTION    = 'https://api.push.apple.com/3/device/';

	public const APNS_ENVIRONMENT_SANDBOX = 'sandbox';
	public const APNS_ENDPOINT_SANDBOX    = 'https://api.development.push.apple.com/3/device/';

	/** @var APNSCredentials */
	private $credentials;

	/** @var string|null */
	private $user_agent = null;

	/** @var string */
	private $environment;

	/** @var string|null */
	private $current_token = null;

	/** @var int */
	private $expires = 0;

	/**
	 * How frequently to refresh the auth token. Specified in seconds.
	 *
	 * @var int
	 */
	private $token_refresh_interval = 1800;

	/** @var APNSTokenFactory */
	private $token_factory;

	protected function __construct( APNSCredentials $credentials, string $environment, ?APNSTokenFactory $factory ) {
		$this->credentials   = $credentials;
		$this->environment   = $environment;
		$this->token_factory = $factory ?? new APNSDefaultTokenFactory();
	}

	public static function production( APNSCredentials $credentials, ?APNSTokenFactory $factory = null ): self {
		return new APNSConfiguration( $credentials, self::APNS_ENVIRONMENT_PRODUCTION, $factory );
	}

	public static function sandbox( APNSCredentials $credentials, ?APNSTokenFactory $factory = null ): self {
		return new APNSConfiguration( $credentials, self::APNS_ENVIRONMENT_SANDBOX, $factory );
	}

	public function get_environment(): string {
		return $this->environment;
	}

	public function get_provider_token(): string {

		if ( ! is_null( $this->current_token ) && $this->expires > time() ) {
			return $this->current_token;
		}

		$current_token = $this->token_factory->get_token(
			$this->credentials->get_team_id(),
			$this->credentials->get_key_id(),
			$this->credentials->get_key_bytes()
		);

		$this->expires       = time() + $this->token_refresh_interval;
		$this->current_token = $current_token;

		return $current_token;
	}

	public function get_user_agent(): ?string {
		return $this->user_agent;
	}

	public function set_user_agent( string $user_agent ): self {
		$this->user_agent = $user_agent;
		return $this;
	}

	public function get_token_refresh_interval(): int {
		return $this->token_refresh_interval;
	}

	// Must be between 20 and 60 minutes:
	// Refresh your token no more than once every 20 minutes and no less than once every 60 minutes. APNs rejects any request whose token contains a timestamp that is more than one hour old. Similarly, APNs reports an error if you recreate your tokens more than once every 20 minutes.
	// Source: https://developer.apple.com/documentation/usernotifications/setting_up_a_remote_notification_server/establishing_a_token-based_connection_to_apns
	public function set_token_refresh_interval( int $interval ): self {

		if ( $interval < 1260 ) {
			throw new InvalidArgumentException( 'Invalid Token Refresh interval: ' . $interval . '. It must be greater than 21 minutes and less than 59 minutes (specified in seconds)' );
		}

		if ( $interval > 3540 ) {
			throw new InvalidArgumentException( 'Invalid Token Refresh interval: ' . $interval . '. It must be less than 59 minutes and greater than 21 minutes (specified in seconds)' );
		}

		$this->token_refresh_interval = $interval;

		return $this;
	}

	public function get_endpoint(): string {
		if ( self::APNS_ENVIRONMENT_PRODUCTION === $this->environment ) {
			return self::APNS_ENDPOINT_PRODUCTION;
		}

		if ( self::APNS_ENVIRONMENT_SANDBOX === $this->environment ) {
			return self::APNS_ENDPOINT_SANDBOX;
		}

		throw new OutOfBoundsException( 'Unable to determine endpoint for environment' );
	}
}
