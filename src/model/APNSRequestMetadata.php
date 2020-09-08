<?php
declare( strict_types = 1 );

class APNSRequestMetadata {

	/** @var string */
	private $topic;

	/** @var string */
	private $push_type = APNSPushType::ALERT;

	/** @var int */
	private $expiration_timestamp = 0;

	/** @var int */
	private $priority = APNSPriority::IMMEDIATE;

	/** @var string | null */
	private $collapse_identifier = null;

	/** @var string */
	private $uuid;

	public function __construct( string $topic, ?string $uuid = null ) {
		$this->topic = $topic;
		$this->uuid = $uuid ?? $this->generate_uuid();
	}

	public static function withTopic( string $topic ): self {
		return new APNSRequestMetadata( $topic );
	}

	public function getTopic(): string {
		return $this->topic;
	}

	public function setTopic( string $topic ): self {

		if ( empty( trim( $topic ) ) ) {
			throw new InvalidArgumentException( 'The topic ' . $topic . ' must not be empty' );
		}

		$this->topic = $topic;
		return $this;
	}

	public function getPushType(): string {
		return $this->push_type;
	}

	public function setPushType( string $type ): self {
		if ( ! APNSPushType::isValid( $type ) ) {
			throw new InvalidArgumentException( 'Invalid Push Type: ' . $type );
		}

		$this->push_type = $type;
		return $this;
	}

	/**
	 * Retrieves the expiration timestamp for this push notification.
	 *
	 * The default value is zero, which means the push notification will be discarded immediately if it is undeliverable.
	 *
	 * @return int
	 */
	public function getExpirationTimestamp(): int {
		return $this->expiration_timestamp;
	}

	/**
	 * Sets the expiration timestamp for this push notification.
	 *
	 * @param int $timestamp A UNIX timestamp expressed in seconds (UTC) identifying the date at which the notification is no longer valid and can be discarded.
	 * If this value is nonzero, APNs stores the notification and tries to deliver it at least once, repeating the attempt as needed if it is unable to deliver the notification the first time. If the value is 0, APNs treats the notification as if it expires immediately and does not store the notification or attempt to redeliver it.
	 * @return APNSRequestMetadata
	 */
	public function setExpirationTimestamp( int $timestamp ): self {
		$this->expiration_timestamp = $timestamp;
		return $this;
	}

	public function getPriority(): int {
		return $this->priority;
	}

	/**
	 * Set the push notification to be delivered with normal priority.
	 *
	 * Normal priority push notifications will be sent immediately.
	 * Notifications with this priority must trigger an alert, sound, or badge on the target device.
	 * It is an error to use this priority for content-available push notification.
	 *
	 * @return APNSRequestMetadata
	 */
	public function setNormalPriority(): self {
		$this->priority = APNSPriority::IMMEDIATE;
		return $this;
	}

	/**
	 * Set the push notification to be delivered with normal priority.
	 *
	 * Send the push message at a time that takes into account power considerations for the device. Notifications with this priority might be grouped and delivered in bursts. They are throttled, and in some cases are not delivered.
	 *
	 * @return APNSRequestMetadata
	 */
	public function setLowPriority(): self {
		$this->priority = APNSPriority::THROTTLED;
		return $this;
	}

	public function getCollapseIdentifier(): ?string {
		return $this->collapse_identifier;
	}

	/**
	 * Set the collapse identifier for this push notification.
	 *
	 * Multiple notifications with the same collapse identifier are displayed to the user as a single notification.
	 * The length of this identifier must not exceed 64 bytes.
	 * @return self
	 */
	public function setCollapseIdentifier( string $identifier ): self {

		if ( strlen( $identifier ) > 64 ) {
			throw new InvalidArgumentException( 'The collapse identifier ' . $identifier . ' is greater than 64 byes in length' );
		}

		$this->collapse_identifier = $identifier;
		return $this;
	}

	public function getUuid(): string {
		return $this->uuid;
	}

	public function setUuid( string $uuid ): self {
		$this->uuid = $uuid;
		return $this;
	}

	// Copied from WordPress
	private function generate_uuid(): string {
		return sprintf(
			'%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
			mt_rand( 0, 0xffff ),
			mt_rand( 0, 0xffff ),
			mt_rand( 0, 0xffff ),
			mt_rand( 0, 0x0fff ) | 0x4000,
			mt_rand( 0, 0x3fff ) | 0x8000,
			mt_rand( 0, 0xffff ),
			mt_rand( 0, 0xffff ),
			mt_rand( 0, 0xffff )
		);
	}

	public function toJSON(): string {
		$object = [
			'topic' => $this->topic,
			'uuid' => $this->uuid,
		];

		if ( $this->push_type !== APNSPushType::ALERT ) {
			$object['push_type'] = $this->push_type;
		}

		if ( $this->expiration_timestamp !== 0 ) {
			$object['expiration_timestamp'] = $this->expiration_timestamp;
		}

		if ( $this->priority !== APNSPriority::IMMEDIATE ) {
			$object['priority'] = $this->priority;
		}

		if ( ! is_null( $this->collapse_identifier ) ) {
			$object['collapse_identifier'] = $this->collapse_identifier;
		}

		return json_encode( $object );
	}

	public static function fromJSON( string $data ): self {
		$object = (object) json_decode( $data, false, 512, JSON_THROW_ON_ERROR );

		if ( ! property_exists( $object, 'topic' ) || is_null( $object->topic ) || ! is_string( $object->topic ) ) {
			throw new InvalidArgumentException( 'Unable to unserialize object – `topic` is not present' );
		}

		if ( ! property_exists( $object, 'uuid' ) || is_null( $object->uuid ) || ! is_string( $object->uuid ) ) {
			throw new InvalidArgumentException( 'Unable to unserialize object – `uuid` is not present' );
		}

		$metadata = new APNSRequestMetadata( $object->topic, $object->uuid );

		if ( property_exists( $object, 'push_type' ) && ! is_null( $object->push_type ) && is_string( $object->push_type ) ) {
			$metadata->setPushType( $object->push_type );
		}

		if ( property_exists( $object, 'expiration_timestamp' ) && ! is_null( $object->expiration_timestamp ) && is_numeric( $object->expiration_timestamp ) ) {
			$metadata->setExpirationTimestamp( intval( $object->expiration_timestamp ) );
		}

		if ( property_exists( $object, 'priority' ) && ! is_null( $object->priority ) && is_numeric( $object->priority ) ) {
			if ( ! APNSPriority::isValid( intval( $object->priority ) ) ) {
				throw new InvalidArgumentException( 'Unable to unserialize object – `priority` is invalid' );
			}

			if ( $object->priority === APNSPriority::IMMEDIATE ) {
				$metadata->setNormalPriority();
			} elseif ( $object->priority === APNSPriority::THROTTLED ) {
				$metadata->setLowPriority();
			}
		}

		if ( property_exists( $object, 'collapse_identifier' ) && ! is_null( $object->collapse_identifier ) && is_string( $object->collapse_identifier ) ) {
			$metadata->setCollapseIdentifier( $object->collapse_identifier );
		}

		return $metadata;
	}
}
