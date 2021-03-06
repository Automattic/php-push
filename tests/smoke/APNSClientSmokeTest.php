<?php
declare( strict_types = 1 );

/**
 * A smoke test to find basic compilation errors in `APNSClient.php`
 *
 * @covers APNSClient
 */
class APNSClientSmokeTest extends APNSTest {

	public function testThatSetPortNumberWorks() {
		$client = ( new APNSClient( $this->new_configuration() ) )->set_port_number( random_int( 1, 65535 ) );
		$this->assertNotNull( $client );
	}

	public function testThatSetDebugWorks() {
		$client = ( new APNSClient( $this->new_configuration() ) )->set_debug( true );
		$this->assertNotNull( $client );
	}

	public function testThatSetCertificateBundlePathWorks() {
		$client = ( new APNSClient( $this->new_configuration() ) )->set_certificate_bundle_path( dirname( __DIR__ ) . '/MockAPNSServer/test-cert.pem' );
		$this->assertNotNull( $client );
	}

	public function testThatSetCertificateBundlePathThrowsForInvalidPath() {
		$this->expectException( InvalidArgumentException::class );
		( new APNSClient( $this->new_configuration() ) )->set_certificate_bundle_path( $this->random_string() );
	}

	public function testThatCloseWorks() {
		$client = ( new APNSClient( $this->new_configuration() ) )->close();
		$this->assertNotNull( $client );
	}
}
