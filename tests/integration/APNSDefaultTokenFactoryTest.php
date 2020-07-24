<?php
declare( strict_types = 1 );
class APNSDefaultTokenFactoryTest extends APNSTest {

	function testThatTokenContainsExpectedValues() {
		$team_id = $this->random_string( 10 );
		$key_id = $this->random_string( 10 );

		$factory = new APNSDefaultTokenFactory();
		$token = $factory->get_token( $team_id, $key_id, $this->get_test_resource( 'example-key' ) );

		$token = $this->decodeJWT( $token );

		$this->assertEquals( $team_id, $token->team_id );
		$this->assertEquals( $key_id, $token->key_id );
		$this->assertEquals( time(), $token->time );
	}

	// TODO: Add a test to validate that the key is properly signed (by extracting its public key and trying to decrypt it)

	private function decodeJWT( $token ): object {
		$tks = \explode( '.', $token );
		$this->assertEquals( 3, count( $tks ) );

		list( $headb64, $bodyb64 ) = $tks;

		$headb64 = base64_decode( $headb64 );
		$this->assertNotNull( $headb64 );

		$head = json_decode( $headb64 );
		$this->assertNotNull( $head );

		$bodyb64 = base64_decode( $bodyb64 );
		$this->assertNotNull( $bodyb64 );

		$body = json_decode( $bodyb64 );
		$this->assertNotNull( $body );

		return (object) [
			'key_id' => $head->kid,
			'team_id' => $body->iss,
			'time' => $body->iat,
		];
	}
}
