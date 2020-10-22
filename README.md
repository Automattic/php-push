# php-push

![CI](https://github.com/Automattic/php-push/workflows/CI/badge.svg)
[![Test Coverage](https://api.codeclimate.com/v1/badges/3231adb560db705c221a/test_coverage)](https://codeclimate.com/github/Automattic/php-push/test_coverage)

This library provides the ability to send Push Notifications to Apple using the new JSON-based endpoint.

## Philosophy 
This library aims to be:

**Lightweight**

There's not a lot here – just enough classes to model the API and provide a client. Bring your own business logic, job queue, and framework integrations.

**Opinionated**

The library assumes you want:
- Type hinted everything. We're careful about what we accept and explicit about what we return.
- Validation when creating push notification payloads (instead of having them rejected after being sent). This makes issues easier to find in development. 
- Separation of notification scheduling and delivery. While it's possible to build and send a notification all at once, we assume you're using some sort of worker process to handle delivery to Apple. Because of this, the library provides serialization and deserialization.
- As few runtime dependencies as possible, and it works just as well whether you're using composer or not.

**Fast**

The library is designed to maximize queue throughput and minimize latency – to do this, it moves as much processing as possible out of the queue worker into the scheduling logic.

## System Requirements
- PHP 7.3 or greater
- PHP curl extension version 7.61 or greater
- PHP curl compiled with HTTP/2 support

## Common Issues

### `CURLE_PEER_FAILED_VERIFICATION` (error 60)

Debian removed the Geotrust certificate that Apple relies on [in the `20200601` ca-certificates update](https://bugs.debian.org/cgi-bin/bugreport.cgi?bug=962596). The best workaround until that's fixed is to use the another certificate bundle, such as [the one provided by curl](https://curl.haxx.se/docs/caextract.html). You can do this by using:

```php
$client = APNSClient::withConfiguration($configuration)->setCertificateBundlePath('/path/to/cacert.pem');
```
