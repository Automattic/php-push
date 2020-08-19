<?php
declare( strict_types = 1 );

/**
 * A smoke test to find basic compilation errors in `APNSClient.php`
 *
 * @covers APNSClient
 */
class APNSClientSmokeTest extends APNSTest {

	public function testThatSetPortNumberWorks() {
		$client = ( new APNSClient( $this->new_configuration() ) )->setPortNumber( random_int( 1, 65535 ) );
		$this->assertNotNull( $client );
	}

	public function testThatSetDebugWorks() {
		$client = ( new APNSClient( $this->new_configuration() ) )->setDebug( true );
		$this->assertNotNull( $client );
	}

	public function testThatSetDisableSSLWorks() {
		$client = ( new APNSClient( $this->new_configuration() ) )->setDisableSSLVerification( true );
		$this->assertNotNull( $client );
	}

	public function testThatSetCertificateBundlePathWorks() {
		$client = ( new APNSClient( $this->new_configuration() ) )->setCertificateBundlePath( $this->random_string() );
		$this->assertNotNull( $client );
	}

	public function testThatCloseWorks() {
		$client = ( new APNSClient( $this->new_configuration() ) )->close();
		$this->assertNotNull( $client );
	}
}
