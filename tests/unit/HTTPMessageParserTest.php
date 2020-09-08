<?php
declare( strict_types = 1 );

class HTTPMessageParserTest extends APNSTest {

	public function testThatParserReadsArbitraryResponses() {
		$data = $this->get_test_resource( 'valid-http-1-response' );
		$parser = new HTTPMessageParser( $data );
		$this->assertEquals( 'HTTP/1.1', $parser->getHttpVersion() );
		$this->assertEquals( 200, $parser->getStatusCode() );
		$this->assertEquals( 'Sun, 26 Sep 2010 22:04:35 GMT', $parser->getHeader( 'Last-Modified' ) );
		$this->assertEquals( 'Hello world!', $parser->getBody() );
	}

	public function testThatParserReadsAPNSErrorResponses() {
		$data = $this->get_test_resource( '400-bad-device-token-response' );
		$parser = new HTTPMessageParser( $data );
		$this->assertEquals( 'HTTP/2', $parser->getHttpVersion() );
		$this->assertEquals( 400, $parser->getStatusCode() );
		$this->assertEquals( '8FE746FE-1112-2966-3590-2DC3F038536B', $parser->getHeader( 'apns-id' ) );
		$this->assertEquals( '{"reason":"BadDeviceToken"}', $parser->getBody() );
	}

	public function testThatParserThrowsForMissingHTTPHeader() {
		$this->expectException( InvalidArgumentException::class );
		$data = $this->get_test_resource( 'missing-http-header-response' );
		new HTTPMessageParser( $data );
	}
}
