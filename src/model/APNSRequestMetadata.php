<?php
declare( strict_types = 1 );

class APNSRequestMetadata {

	private $push_type = APNSPushType::ALERT;
	private $expiration = 0;
	private $priority = APNSPriority::IMMEDIATE;
	private $collapse_identifier = null;
	private $topic;
	private $uuid = null;

	function __construct( string $topic, string $uuid = null ) {
		$this->topic = $topic;

		if ( is_null( $uuid ) ) {
			$uuid = $this->generate_uuid();
		}

		$this->uuid = $uuid;
	}

	function getTopic() {
		return $this->topic;
	}

	function setTopic( string $topic ) {

		if ( empty( trim( $topic ) ) ) {
			throw new InvalidArgumentException( 'The topic ' . $topic . ' must not be empty' );
		}

		$this->topic = $topic;
		return $this;
	}

	function getPushType(): string {
		return $this->push_type;
	}

	function setPushType( string $type ) {
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
	function getExpirationTimestamp() {
		return $this->expiration;
	}

	/**
	 * Sets the expiration timestamp for this push notification.
	 *
	 * @param int $timestamp A UNIX timestamp expressed in seconds (UTC) identifying the date at which the notification is no longer valid and can be discarded.
	 * If this value is nonzero, APNs stores the notification and tries to deliver it at least once, repeating the attempt as needed if it is unable to deliver the notification the first time. If the value is 0, APNs treats the notification as if it expires immediately and does not store the notification or attempt to redeliver it.
	 * @return Current_Class_Name
	 */
	function setExpirationTimestamp( int $timestamp ) {
		$this->expiration = $timestamp;
		return $this;
	}

	function getPriority() {
		return $this->priority;
	}

	/**
	 * Set the push notification to be delivered with normal priority.
	 *
	 * Normal priority push notifications will be sent immediately.
	 * Notifications with this priority must trigger an alert, sound, or badge on the target device.
	 * It is an error to use this priority for content-available push notification.
	 *
	 * @return Current_Class_Name
	 */
	function setNormalPriority() {
		$this->priority = APNSPriority::IMMEDIATE;
		return $this;
	}

	/**
	 * Set the push notification to be delivered with normal priority.
	 *
	 * Send the push message at a time that takes into account power considerations for the device. Notifications with this priority might be grouped and delivered in bursts. They are throttled, and in some cases are not delivered.
	 *
	 * @return Current_Class_Name
	 */
	function setLowPriority() {
		$this->priority = APNSPriority::THROTTLED;
		return $this;
	}

	function getCollapseIdentifier(): ?string {
		return $this->collapse_identifier;
	}

	/**
	 * Set the collapse identifier for this push notification.
	 *
	 * Multiple notifications with the same collapse identifier are displayed to the user as a single notification.
	 * The length of this identifier must not exceed 64 bytes.
	 */
	function setCollapseIdentifier( string $identifier ) {

		if ( strlen( $identifier ) > 64 ) {
			throw new InvalidArgumentException( 'The collapse identifier ' . $identifier . ' is greater than 64 byes in length' );
		}

		$this->collapse_identifier = $identifier;
		return $this;
	}

	function getUuid(): string {
		return $this->uuid;
	}

	function setUuid( string $uuid ) {
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
}
