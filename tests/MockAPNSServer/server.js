const http2  = require( "http2" );
const fs     = require( "fs" );
const crypto = require( "crypto" );

function uuidv4() {
	const bytes = crypto.randomBytes( 16 );
	bytes[6]    = ( bytes[6] & 0x0f ) | 0x40;
	bytes[8]    = ( bytes[8] & 0x3f ) | 0x80;

	const hex = bytes.toString( "hex" );
	return [
		hex.slice( 0, 8 ),
		hex.slice( 8, 12 ),
		hex.slice( 12, 16 ),
		hex.slice( 16, 20 ),
		hex.slice( 20 ),
	].join( "-" );
}

const server = http2.createSecureServer(
	{
		key: fs.readFileSync( "test-key.pem" ),
		cert: fs.readFileSync( "test-cert.pem" ),
	}
);

server.on(
	"error",
	function(err){
		console.error( err )
	}
);

var requestNumber = 0;
var sessions      = [];

server.on(
	"session",
	function (session) {
		sessions.push( session );
	}
);

server.on(
	"stream",
	function(stream, headers) {
		stream.respond(
			{
				"content-type": "text/html; charset=utf-8",
				":status": 200,
				"apns-id": uuidv4(),
			}
		);
		if (headers[":path"] === "/") {
			console.log( "Request:", headers );
			requestNumber++;
			stream.end( "Request Count: " + requestNumber );
		}
		if (headers[":path"] === "/reset") {
			console.log( "Resetting Sessions. Current Session Count: " + sessions.length );

			const sessionsCopy = sessions.filter(
				function(session) {
					return session !== stream.session
				}
			); // Reset all the other sessions except the one for this request

			sessionsCopy.forEach(
				function (session) {
					const reason   = Buffer.from( '{"reason": "Foo"}', "utf8" );
					const streamId = session.state.lastProcStreamID;
					if ( ! session.closed) {
						session.goaway( http2.constants.NGHTTP2_NO_ERROR, streamId, reason );
					}
				}
			);

			this.sessions = [stream.session];
			stream.end( `Reset Complete: Current Sessions Count: ${this.sessions.length}` );
		}
	}
);

server.listen( 8443 );
console.log(`Server running at https://127.0.0.1:8443`);
