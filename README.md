# php-push

This library provides the ability to send Push Notifications to Apple using the new JSON-based endpoint.

## Philosophy 
This library aims to be:

**Lightweight**

There's not a lot here – just enough classes to model the API and provide a client. Bring your own business logic, job queue, and framework integrations.

**Opinionated**

The library assumes you want:
- Type hinted everything. We're careful about what we accept.
- Validation when creating push objects (instead of having them rejected after being sent). This makes issues easier to find in development. 
- Separation of notification scheduling and delivery. While it's possible to schedule and send a notification all at once, we assume you're using some sort of worker process to handle delivery to Apple. Because of this, the library provides serialization and deserialization.
- As few runtime dependencies as possible. It works great in a non-composer environment, though you can of course use composer to install it.

**Fast**

The library is designed to maximize queue throughput – to do this, it moves as processing as possible out of the queue worker into the scheduling logic.

## System Requirements
- PHP 7.3 or greater
- PHP curl extension version 7.61 or greater
- PHP curl compiled with HTTP/2 support