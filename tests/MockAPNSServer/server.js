const http2 = require("http2");
const fs = require("fs");
const uuid = require("uuid");

const server = http2.createSecureServer({
  key: fs.readFileSync("test-key.pem"),
  cert: fs.readFileSync("test-cert.pem"),
});
server.on("error", (err) => console.error(err));

var requestNumber = 0;
var sessions = [];

server.on("session", (session) => {
  sessions.push(session);
});

server.on("stream", (stream, headers) => {

  stream.respond({
    "content-type": "text/html; charset=utf-8",
    ":status": 200,
    "apns-id": uuid.v4(),
  });

  if (headers[":path"] === "/") {
    console.log("Request:", headers);
    requestNumber++;
    stream.end("Request Count: " + requestNumber);
  }

  if (headers[":path"] === "/reset") {
    console.log("Resetting Sessions. Current Session Count: " + sessions.length);

    const sessionsCopy = sessions.filter(
      (session) => session !== stream.session
    ); // Reset all the other sessions except the one for this request

    sessionsCopy.forEach((session) => {
      const reason = Buffer.from('{"reason": "Foo"}', "utf8");
      const streamId = session.state.lastProcStreamID;

      if (!session.closed) {
        session.goaway(http2.constants.NGHTTP2_NO_ERROR, streamId, reason);
      }
    });

    sessions = [stream.session];
    stream.end(`Reset Complete: Current Sessions Count: ${sessions.length}`);
  }
});

server.listen(8443);
console.log(`Server running at https://127.0.0.1:8443`);
