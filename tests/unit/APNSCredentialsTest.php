<?php
declare( strict_types = 1 );
class APNSCredentialsTest extends APNSTest {

	public function testThatAPNSCredentialInstantiationProperlyStoresValues() {
		$key_id    = $this->random_string( 10 );
		$team_id   = $this->random_string( 10 );
		$key_bytes = random_bytes( 32 );

		$credentials = new APNSCredentials( $key_id, $team_id, $key_bytes );
		$this->assertEquals( $key_id, $credentials->get_key_id() );
		$this->assertEquals( $team_id, $credentials->get_team_id() );
		$this->assertEquals( $key_bytes, $credentials->get_key_bytes() );
	}

	public function testThatSandboxInitializerThrowsForInvalidKeyId() {
		$key_id    = $this->random_string( 11 );
		$team_id   = $this->random_string( 10 );
		$key_bytes = random_bytes( 32 );

		$this->expectException( InvalidArgumentException::class );
		new APNSCredentials( $key_id, $team_id, $key_bytes );
	}

	public function testThatSandboxInitializerThrowsForInvalidTeamId() {
		$key_id    = $this->random_string( 10 );
		$team_id   = $this->random_string( 11 );
		$key_bytes = random_bytes( 32 );

		$this->expectException( InvalidArgumentException::class );
		new APNSCredentials( $key_id, $team_id, $key_bytes );
	}
}
