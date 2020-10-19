<?php
declare( strict_types = 1 );

class HTTPMessageParserTest extends APNSTest {

	public function testThatParserReadsArbitraryResponses() {
		$data   = $this->get_test_resource( 'valid-http-1-response' );
		$parser = new HTTPMessageParser( $data );
		$this->assertEquals( 'HTTP/1.1', $parser->get_http_version() );
		$this->assertEquals( 200, $parser->get_status_code() );
		$this->assertEquals( 'Sun, 26 Sep 2010 22:04:35 GMT', $parser->get_header( 'Last-Modified' ) );
		$this->assertEquals( 'Hello world!', $parser->get_body() );
	}

	public function testThatParserReadsAPNSErrorResponses() {
		$data   = $this->get_test_resource( '400-bad-device-token-response' );
		$parser = new HTTPMessageParser( $data );
		$this->assertEquals( 'HTTP/2', $parser->get_http_version() );
		$this->assertEquals( 400, $parser->get_status_code() );
		$this->assertEquals( '8FE746FE-1112-2966-3590-2DC3F038536B', $parser->get_header( 'apns-id' ) );
		$this->assertEquals( '{"reason":"BadDeviceToken"}', $parser->get_body() );
	}

	public function testThatParserThrowsForMissingHTTPHeader() {
		$this->expectException( InvalidArgumentException::class );
		$data = $this->get_test_resource( 'missing-http-header-response' );
		new HTTPMessageParser( $data );
	}

	public function testThatParserReturnsHttp2ForEmptyHttpResponse() {
		$this->assertSame( '2.0', ( new HTTPMessageParser( '' ) )->get_http_version() );
	}

	public function testThatParserReturnsZeroStatusCodeForEmptyHttpResponse() {
		$this->assertSame( 0, ( new HTTPMessageParser( '' ) )->get_status_code() );
	}

	public function testThatParserReturnsEmptyBodyForEmptyHttpResponse() {
		$this->assertSame( '', ( new HTTPMessageParser( '' ) )->get_body() );
	}

	public function testThatParserReturnsNullAPNSIdHeaderForEmptyHttpResponse() {
		$this->assertNull( ( new HTTPMessageParser( '' ) )->get_header( 'apns-id' ) );
	}
}
